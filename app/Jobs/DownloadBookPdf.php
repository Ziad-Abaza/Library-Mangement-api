<?php

namespace App\Jobs;

use App\Models\Book;
use App\Models\Download;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadBookPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bookId;
    protected $userId;

    public function __construct(int $bookId, int $userId = null)
    {
        $this->bookId = $bookId;
        $this->userId = $userId;
    }

    public function handle()
    {
        try {
            $book = Book::find($this->bookId);
            if (!$book) {
                throw new Exception('The specified book does not exist.');
            }

            $fileUrl = $book->getFirstMediaPath('file');
            if (!$fileUrl || !Storage::exists($fileUrl)) {
                throw new Exception('File not found.');
            }

            $book->increment('downloads_count');

            if ($this->userId) {
                Download::create([
                    'user_id' => $this->userId,
                    'book_id' => $book->id,
                ]);
            }

            Log::info('Download job processed successfully', [
                'book_id' => $book->id,
                'user_id' => $this->userId,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to handle book download', [
                'book_id' => $this->bookId ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
