<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Book;
use Smalot\PdfParser\Parser;

class ProcessBookUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $book;
    protected $file;
    protected $request;

    public function __construct(Book $book, $file, $request)
    {
        $this->book = $book;
        $this->file = $file;
        $this->request = $request;
    }

    public function handle()
    {
        // تحليل ملف الـ PDF للحصول على عدد الصفحات
        $pdfParser = new Parser();
        $pdf = $pdfParser->parseFile($this->file->getRealPath());
        $numberOfPages = count($pdf->getPages());

        // حساب حجم الملف بالميغابايت
        $sizeInMB = $this->file->getSize() / (1024 * 1024);
        $size = round($sizeInMB, 2);

        // تحديث بيانات الكتاب
        $this->book->update([
            'number_pages' => $numberOfPages,
            'size' => $size,
        ]);

        // معالجة رفع الملفات (الصورة الغلاف، صورة الحقوق، ملف الكتاب)
        $this->handleMediaUploads();

        // مسح الذاكرة المؤقتة
        $this->clearCache();
    }

    private function handleMediaUploads()
    {
        if ($this->request->hasFile('cover_image')) {
            $this->book->clearMediaCollection('cover_image');
            $this->book->addMedia($this->request->file('cover_image'))->toMediaCollection('cover_image');
        }

        if ($this->request->hasFile('file')) {
            $this->book->clearMediaCollection('file');
            $this->book->addMedia($this->request->file('file'))->toMediaCollection('file');
        }

        if ($this->request->hasFile('copyright_image')) {
            $this->book->clearMediaCollection('copyright_image');
            $this->book->addMedia($this->request->file('copyright_image'))->toMediaCollection('copyright_image');
        }
    }

    private function clearCache()
    {
        \Illuminate\Support\Facades\Cache::forget("book_{$this->book->id}");
    }
}
