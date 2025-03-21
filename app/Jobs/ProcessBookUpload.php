<?php

namespace App\Jobs;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Smalot\PdfParser\Parser;

class ProcessBookUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $book;
    protected $file;
    protected $coverImage;
    protected $copyrightImage;

    /**
     * Create a new job instance.
     */
    public function __construct(Book $book, $file, $coverImage = null, $copyrightImage)
    {
        $this->book = $book;
        $this->file = $file;
        $this->coverImage = $coverImage;
        $this->copyrightImage = $copyrightImage;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if ($this->file) {
            $sizeInMB = $this->file->getSize() / (1024 * 1024);
            $pdfParser = new Parser();
            $pdf = $pdfParser->parseFile($this->file->getRealPath());
            $numberOfPages = count($pdf->getPages());

            $this->book->update([
                'number_pages' => $numberOfPages,
                'size' => round($sizeInMB, 2),
            ]);

            $this->book->addMedia($this->file)->toMediaCollection('file');
        }

        if ($this->coverImage) {
            $this->book->addMedia($this->coverImage)->toMediaCollection('cover_image');
        }

        if ($this->copyrightImage) {
            $this->book->addMedia($this->copyrightImage)->toMediaCollection('copyright_image');
        }
    }
}
