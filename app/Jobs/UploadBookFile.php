<?php

namespace App\Jobs;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class UploadBookFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $book;
    protected $file;

    /**
     * إنشاء Job جديد
     */
    public function __construct(Book $book, UploadedFile $file)
    {
        $this->book = $book;
        $this->file = $file;
    }

    /**
     * تنفيذ Job رفع الملف
     */
    public function handle()
    {
        try {
            // رفع الملف باستخدام Spatie Media Library
            $this->book->addMedia($this->file)->toMediaCollection('file');

            // حساب حجم الملف بالميجابايت
            $sizeInMB = $this->file->getSize() / (1024 * 1024);

            // تحليل ملف PDF لحساب عدد الصفحات
            $pdfParser = new Parser();
            $pdf = $pdfParser->parseFile($this->file->getRealPath());
            $numberOfPages = count($pdf->getPages());

            // تحديث بيانات الكتاب
            $this->book->update([
                'size' => round($sizeInMB, 2),
                'number_pages' => $numberOfPages,
            ]);
        } catch (\Exception $e) {
            Log::error("فشل رفع الملف: " . $e->getMessage());
        }
    }
}
