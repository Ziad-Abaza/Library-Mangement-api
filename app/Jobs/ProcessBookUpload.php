<?php

namespace App\Jobs;

use App\Models\Book;
use Smalot\PdfParser\Parser;
use App\Http\Controllers\Api\BookController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBookUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $book;
    protected $file;

    public function __construct(Book $book, $file)
    {
        $this->book = $book;
        $this->file = $file;
    }

    public function handle()
    {
        // Calculate file size in MB
        $sizeInMB = $this->file->getSize() / (1024 * 1024);
        $this->book->size = round($sizeInMB, 2);

        // Parse the PDF to get number of pages
        $pdfParser = new Parser();
        $pdf = $pdfParser->parseFile($this->file->getRealPath());
        $numberOfPages = count($pdf->getPages());

        // Update the book with number of pages and size
        $this->book->update([
            'number_pages' => $numberOfPages,
            'size' => $this->book->size,
        ]);

        // Handle media uploads like cover image, copyright image
        $bookController = new BookController();
        $bookController->handleMediaUploads($this->book, $this->file);
    }
}
