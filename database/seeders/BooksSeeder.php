<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookSeries;
use Illuminate\Database\Seeder;

class BooksSeeder extends Seeder
{
    public function run()
    {
        $bookSeries = [
            [
                'title' => 'ثلاثية القاهرة',
                'description' => 'ثلاثية أدبية شهيرة كتبها نجيب محفوظ، تتكون من "بين القصرين"، "قصر الشوق"، و"السكرية"، وتتناول تطور المجتمع المصري من خلال حياة أسرة السيد أحمد عبد الجواد.',
                'user_id' => 1,
                'image' => 'cairo_trilogy.jpeg',
            ],
            [
                'title' => 'سلسلة العبقريات',
                'description' => 'مجموعة كتب من تأليف عباس محمود العقاد تتناول تحليل شخصيات بارزة في التاريخ الإسلامي، مثل "عبقرية محمد"، "عبقرية عمر"، و"عبقرية خالد".',
                'user_id' => 1,
                'image' => 'abkariyat.jpeg',
            ],
            [
                'title' => 'ثلاثية غرناطة',
                'description' => 'رواية تاريخية لأحلام مستغانمي تتناول سقوط الأندلس من خلال ثلاثية أدبية: "غرناطة"، "مريمة"، و"الرحيل".',
                'user_id' => 1,
                'image' => 'granada_trilogy.jpeg',
            ],
            [
                'title' => 'ديوان الشوقيات',
                'description' => 'مجموعة شعرية ضخمة من تأليف أحمد شوقي، تشمل قصائد في مختلف المجالات الأدبية والاجتماعية والوطنية.',
                'user_id' => 1,
                'image' => 'shawqiyat.jpeg',
            ],
            [
                'title' => 'أوراق الزيتون',
                'description' => 'مجموعة شعرية لمحمود درويش تتناول القضايا الفلسطينية ومعاناة الإنسان العربي في ظل الاحتلال.',
                'user_id' => 1,
                'image' => 'olive_leaves.jpeg',
            ],
            [
                'title' => 'ثلاثية الكتاب',
                'description' => 'عمل أدبي للشاعر السوري أدونيس، يعكس فيه تطور الفكر واللغة العربية من خلال مقالاته وأشعاره النقدية.',
                'user_id' => 1,
                'image' => 'adonis_trilogy.jpeg',
            ],
            [
                'title' => 'حكايات حارتنا',
                'description' => 'مجموعة قصصية لنجيب محفوظ تحكي عن الحياة اليومية في الأحياء المصرية التقليدية وتعكس طبيعة المجتمع المصري.',
                'user_id' => 1,
                'image' => 'haretna_stories.jpeg',
            ]
        ];

        foreach ($bookSeries as $seriesData) {
            $bookSeries = BookSeries::create([
                'title' => $seriesData['title'],
                'description' => $seriesData['description'],
                'user_id' => $seriesData['user_id'],
            ]);

            $imagePath = public_path("assets/images/BookSeries/" . $seriesData['image']);
            if (file_exists($imagePath)) {
                $bookSeries->addMedia($imagePath)->toMediaCollection('book_series');
            }
        }

        $books = [
            [
                'title' => 'في ظلال القرآن',
                'description' => 'كتاب يشرح معاني القرآن الكريم بأسلوب أدبي.',
                'published_at' => '1987',
                'number_pages' => 400,
                'size' => 1.5,
                'views_count' => 1000,
                'downloads_count' => 500,
                'edition_number' => '1',
                'lang' => 'العربية',
                'publisher_name' => 'دار الفكر',
                'status' => 'approved',
                'category_id' => 1,
                'user_id' => 1,
                'author_id' => 1,
                'book_series_id' => 1,
                'image' => 'in_the_shadows_of_quran.jpeg',
            ],
            [
                'title' => 'ألف ليلة وليلة',
                'description' => 'مجموعة من الحكايات الشعبية القديمة.',
                'published_at' => '2000',
                'number_pages' => 300,
                'size' => 2.0,
                'views_count' => 1500,
                'downloads_count' => 700,
                'edition_number' => '2',
                'lang' => 'العربية',
                'publisher_name' => 'المؤسسة العربية',
                'status' => 'approved',
                'category_id' => 2,
                'user_id' => 1,
                'author_id' => 1,
                'book_series_id' => 2,
                'image' => 'one_thousand_and_one_nights.jpeg',
            ],
            [
                'title' => 'أبطال الفضاء',
                'description' => 'كتاب يتناول قصص عن رواد الفضاء.',
                'published_at' => '2010',
                'number_pages' => 200,
                'size' => 1.0,
                'views_count' => 500,
                'downloads_count' => 250,
                'edition_number' => '1',
                'lang' => 'العربية',
                'publisher_name' => 'الناشرون',
                'status' => 'approved',
                'category_id' => 3,
                'user_id' => 1,
                'author_id' => 2,
                'book_series_id' => 1,
                'image' => 'space_heroes.jpeg',
            ],
            [
                'title' => 'قصص الأنبياء',
                'description' => 'تتناول قصص حياة الأنبياء وما واجهوه من تحديات.',
                'published_at' => '2015',
                'number_pages' => 350,
                'size' => 1.8,
                'views_count' => 800,
                'downloads_count' => 400,
                'edition_number' => '1',
                'lang' => 'العربية',
                'publisher_name' => 'دار الفاروق',
                'status' => 'approved',
                'category_id' => 1,
                'user_id' => 1,
                'author_id' => 3,
                'book_series_id' => 1,
                'image' => 'stories_of_the_prophets.jpeg',
            ],
            [
                'title' => 'الأدب العربي الحديث',
                'description' => 'يتناول الأدب العربي الحديث ومؤلفيه البارزين.',
                'published_at' => '2020',
                'number_pages' => 250,
                'size' => 1.2,
                'views_count' => 600,
                'downloads_count' => 300,
                'edition_number' => '1',
                'lang' => 'العربية',
                'publisher_name' => 'مكتبة الأنجلو المصرية',
                'status' => 'approved',
                'category_id' => 4,
                'user_id' => 1,
                'author_id' => 4,
                'book_series_id' => 2,
                'image' => 'modern_arab_literature.jpeg',
            ],
            [
                'title' => 'فلسفة التعليم',
                'description' => 'كتاب يتناول مفاهيم التربية والتعليم.',
                'published_at' => '2018',
                'number_pages' => 200,
                'size' => 1.1,
                'views_count' => 450,
                'downloads_count' => 220,
                'edition_number' => '1',
                'lang' => 'العربية',
                'publisher_name' => 'دار العلم',
                'status' => 'approved',
                'category_id' => 5,
                'user_id' => 1,
                'author_id' => 5,
                'book_series_id' => 1,
                'image' => 'philosophy_of_education.jpeg',
            ],
            [
                'title' => 'علم النفس الاجتماعي',
                'description' => 'يستعرض الأسس والنظريات في علم النفس الاجتماعي.',
                'published_at' => '2016',
                'number_pages' => 280,
                'size' => 1.4,
                'views_count' => 550,
                'downloads_count' => 275,
                'edition_number' => '1',
                'lang' => 'العربية',
                'publisher_name' => 'مكتبة المعارف',
                'status' => 'approved',
                'category_id' => 6,
                'user_id' => 1,
                'author_id' => 6,
                'book_series_id' => 2,
                'image' => 'social_psychology.jpeg',
            ],
            [
                'title' => 'الاقتصاد الإسلامي',
                'description' => 'يتناول مفاهيم الاقتصاد من منظور إسلامي.',
                'published_at' => '2017',
                'number_pages' => 320,
                'size' => 1.6,
                'views_count' => 700,
                'downloads_count' => 350,
                'edition_number' => '1',
                'lang' => 'العربية',
                'publisher_name' => 'دار الفكر المعاصر',
                'status' => 'approved',
                'category_id' => 7,
                'user_id' => 1,
                'author_id' => 7,
                'book_series_id' => 1,
                'image' => 'islamic_economics.jpeg',
            ],
            [
                'title' => 'التاريخ الإسلامي',
                'description' => 'مقدمة شاملة للتاريخ الإسلامي وتطوره.',
                'published_at' => '2012',
                'number_pages' => 480,
                'size' => 2.5,
                'views_count' => 900,
                'downloads_count' => 500,
                'edition_number' => '1',
                'lang' => 'العربية',
                'publisher_name' => 'مكتبة الثقافة',
                'status' => 'approved',
                'category_id' => 8,
                'user_id' => 1,
                'author_id' => 8,
                'book_series_id' => 2,
                'image' => 'islamic_history.jpeg',
            ],
            [
                'title' => 'الذكاء الاصطناعي وتطبيقاته',
                'description' => 'كتاب يتناول مبادئ الذكاء الاصطناعي وتطبيقاته المختلفة.',
                'published_at' => '2021',
                'number_pages' => 300,
                'size' => 1.7,
                'views_count' => 750,
                'downloads_count' => 400,
                'edition_number' => '1',
                'lang' => 'العربية',
                'publisher_name' => 'دار التقنية الحديثة',
                'status' => 'approved',
                'category_id' => 9,
                'user_id' => 1,
                'author_id' => 9,
                'book_series_id' => 3,
                'image' => 'ai_and_its_applications.jpeg',
            ],
            [
                'title' => 'البرمجة بلغة بايثون',
                'description' => 'دليل شامل للمبتدئين والمحترفين في لغة بايثون.',
                'published_at' => '2019',
                'number_pages' => 400,
                'size' => 2.0,
                'views_count' => 1200,
                'downloads_count' => 600,
                'edition_number' => '1',
                'lang' => 'EN',
                'publisher_name' => 'دار المبرمجين',
                'status' => 'approved',
                'category_id' => 10,
                'user_id' => 1,
                'author_id' => 10,
                'book_series_id' => 3,
                'image' => 'python_programming.jpeg',
            ],
            [
                'title' => 'أساسيات الرياضيات',
                'description' => 'كتاب يشرح مفاهيم الرياضيات الأساسية بأسلوب مبسط.',
                'published_at' => '2015',
                'number_pages' => 350,
                'size' => 1.5,
                'views_count' => 650,
                'downloads_count' => 320,
                'edition_number' => '1',
                'lang' => 'EN',
                'publisher_name' => 'دار العلوم',
                'status' => 'approved',
                'category_id' => 11,
                'user_id' => 1,
                'author_id' => 1,
                'book_series_id' => 4,
                'image' => 'math_basics.jpeg',
            ],
            [
                'title' => 'علم الفلك والكون',
                'description' => 'كتاب يشرح الكون والنجوم والمجرات بطريقة علمية مشوقة.',
                'published_at' => '2016',
                'number_pages' => 280,
                'size' => 1.3,
                'views_count' => 800,
                'downloads_count' => 420,
                'edition_number' => '1',
                'lang' => 'العربية',
                'publisher_name' => 'دار الفضاء',
                'status' => 'approved',
                'category_id' => 2,
                'user_id' => 1,
                'author_id' => 4,
                'book_series_id' => 4,
                'image' => 'astronomy_and_universe.jpeg',
            ],
        ];

        foreach ($books as $bookData) {
            $book = Book::create([
                'title' => $bookData['title'],
                'description' => $bookData['description'],
                'published_at' => $bookData['published_at'],
                'number_pages' => $bookData['number_pages'],
                'size' => $bookData['size'],
                'views_count' => $bookData['views_count'],
                'downloads_count' => $bookData['downloads_count'],
                'edition_number' => $bookData['edition_number'],
                'lang' => $bookData['lang'],
                'publisher_name' => $bookData['publisher_name'],
                'status' => $bookData['status'],
                'category_id' => $bookData['category_id'],
                'user_id' => $bookData['user_id'],
                'author_id' => $bookData['author_id'],
                'book_series_id' => $bookData['book_series_id'],
            ]);

            $imagePath = public_path("assets/images/books/" . $bookData['image']);
            $filePath = public_path("assets/book-test-file.pdf");
            if (file_exists($filePath)) {
                $book->addMedia($filePath)->toMediaCollection('file');
            }
            if (file_exists($imagePath)) {
                $book->addMedia($imagePath)->toMediaCollection('cover_image');
            }
        }
    }
}
