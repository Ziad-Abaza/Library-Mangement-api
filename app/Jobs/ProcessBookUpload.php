<?php

namespace App\Jobs;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class ProcessBookUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $book;
    protected $filePath;
    protected $request;

    /**
     * Create a new job instance.
     */
    public function __construct(Book $book, string $filePath, Request $request)
    {
        $this->book = $book;
        $this->filePath = $filePath;
        $this->request = $request;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // احسب حجم الملف بالميجابايت
            $sizeInMB = filesize($this->filePath) / (1024 * 1024);

            // استخراج عدد الصفحات من ملف PDF
            $pdfParser = new Parser();
            $pdf = $pdfParser->parseFile($this->filePath);
            $numberOfPages = count($pdf->getPages());

            // تحديث بيانات الكتاب في قاعدة البيانات
            $this->book->update([
                'number_pages' => $numberOfPages,
                'size' => round($sizeInMB, 2),
            ]);

            // استدعاء مكتبة الميديا لمعالجة الصور والبيانات الأخرى
            $this->book->handleMediaUploads($this->request, $this->book);
        } catch (\Exception $e) {
            Log::error('Error processing book upload: ' . $e->getMessage());
        }
    }
}
