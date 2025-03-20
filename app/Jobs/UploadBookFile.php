<?php

namespace App\Jobs;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class UploadBookFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $book;
    protected $filePath;

    /**
     * إنشاء Job جديد
     */
    public function __construct(Book $book, string $filePath)
    {
        $this->book = $book;
        $this->filePath = $filePath;
    }

    /**
     * تنفيذ Job رفع الملف
     */
    public function handle()
    {
        try {
            $fullFilePath = storage_path('app/' . $this->filePath); // استرجاع الملف من التخزين

            // رفع الملف باستخدام Spatie Media Library
            $this->book->addMedia($fullFilePath)->toMediaCollection('file');

            // حساب حجم الملف بالميجابايت
            $sizeInMB = filesize($fullFilePath) / (1024 * 1024);

            // تحليل ملف PDF لحساب عدد الصفحات
            $pdfParser = new Parser();
            $pdf = $pdfParser->parseFile($fullFilePath);
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
