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
    protected $filePath;
    protected $coverImagePath;
    protected $copyrightImagePath;

    /**
     * Create a new job instance.
     */
    public function __construct(Book $book, $filePath, $coverImagePath = null, $copyrightImagePath)
    {
        $this->book = $book;
        $this->filePath = $filePath;
        $this->coverImagePath = $coverImagePath;
        $this->copyrightImagePath = $copyrightImagePath;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // استرجاع ملف الـ PDF
        if ($this->filePath) {
            $file = storage_path('app/' . $this->filePath);
            $sizeInMB = filesize($file) / (1024 * 1024);

            $pdfParser = new Parser();
            $pdf = $pdfParser->parseFile($file);
            $numberOfPages = count($pdf->getPages());

            $this->book->update([
                'number_pages' => $numberOfPages,
                'size' => round($sizeInMB, 2),
            ]);

            $this->book->addMedia($file)->toMediaCollection('file');
        }

        // استرجاع صورة الغلاف
        if ($this->coverImagePath) {
            $coverImage = storage_path('app/' . $this->coverImagePath);
            $this->book->addMedia($coverImage)->toMediaCollection('cover_image');
        }

        // استرجاع صورة حقوق النشر
        if ($this->copyrightImagePath) {
            $copyrightImage = storage_path('app/' . $this->copyrightImagePath);
            $this->book->addMedia($copyrightImage)->toMediaCollection('copyright_image');
        }
    }
}
