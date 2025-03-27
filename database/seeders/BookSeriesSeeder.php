<?php

use Illuminate\Database\Seeder;
use App\Models\BookSeries;

class BookSeriesSeeder extends Seeder
{
    public function run()
    {
        $bookSeries = [
            [
                'title' => 'سلسلة كتب الفقه المبسط',
                'description' => 'مجموعة من الكتب التي تشرح الفقه الإسلامي بأسلوب سهل وميسر للمبتدئين والمتخصصين.',
                'user_id' => 1,
            ],
            [
                'title' => 'سلسلة روايات الخيال العلمي',
                'description' => 'مجموعة قصصية تستعرض تصورات علمية مستقبلية وابتكارات تقنية مذهلة.',
                'user_id' => 1,
            ],
            [
                'title' => 'سلسلة كتب التنمية البشرية',
                'description' => 'مجموعة كتب تهدف إلى تطوير الذات وتحفيز الفرد على النجاح في الحياة الشخصية والمهنية.',
                'user_id' => 1,
            ],
            [
                'title' => 'سلسلة الأدب العربي الكلاسيكي',
                'description' => 'مجموعة تحتوي على أشهر الروايات والدواوين الشعرية التي أثرت في تاريخ الأدب العربي.',
                'user_id' => 1,
            ],
            [
                'title' => 'سلسلة كتب تاريخ العلوم',
                'description' => 'كتب تروي تطور العلوم المختلفة عبر العصور وتأثيرها على البشرية.',
                'user_id' => 1,
            ],
        ];

        foreach ($bookSeries as $series) {
            BookSeries::create($series);
        }
    }
}
