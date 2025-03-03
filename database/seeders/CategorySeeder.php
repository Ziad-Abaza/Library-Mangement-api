<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // إضافة التصنيفات الأساسية
        DB::table('category_groups')->insert([
            ['name' => 'الدين الإسلامي والعلوم الشرعية'],
            ['name' => 'التاريخ'],
            ['name' => 'اللغة العربية'],
            ['name' => 'العلوم الاجتماعية'],
            ['name' => 'القانون'],
            ['name' => 'الفلسفة'],
            ['name' => 'علم النفس'],
            ['name' => 'التنمية البشرية'],
            ['name' => 'الإدارة'],
            ['name' => 'الاقتصاد'],
            ['name' => 'السياسة'],
            ['name' => 'الجغرافيا'],
            ['name' => 'العلوم الطبيعية'],
            ['name' => 'الهندسة'],
            ['name' => 'الطب'],
            ['name' => 'الأدب'],
            ['name' => 'البرمجة'],
            ['name' => 'الفنون'], // 18
            ['name' => 'التكنولوجيا'], // 19
            ['name' => 'الرياضة'], // 20
            ['name' => 'البيئة'], // 21
            ['name' => 'الصحة العامة'], // 22
            ['name' => 'السيرة الذاتية'], // 23
            ['name' => 'الطهي'], // 24
            ['name' => 'العلوم الدينية المقارنة'], // 25
        ]);

        // إضافة التصنيفات الفرعية
        DB::table('categories')->insert([
            // الدين الإسلامي والعلوم الشرعية (ID: 1)
            ['name' => 'التفسير', 'category_group_id' => 1],
            ['name' => 'علوم القرآن و أصول التفسير', 'category_group_id' => 1],
            ['name' => 'علوم الفقه و القواعد الفقهية', 'category_group_id' => 1],
            ['name' => 'التجويد و القراءات', 'category_group_id' => 1],
            ['name' => 'كتب السنة', 'category_group_id' => 1],
            ['name' => 'شروح الحديث', 'category_group_id' => 1],
            ['name' => 'أصول الفقه', 'category_group_id' => 1],
            ['name' => 'الفتاوى', 'category_group_id' => 1],
            ['name' => 'الدعوة الإسلامية', 'category_group_id' => 1],

            // التاريخ (ID: 2)
            ['name' => 'تاريخ الدول و الحضارات', 'category_group_id' => 2],
            ['name' => 'الحروب و النزاعات والعلوم العسكرية والحربية', 'category_group_id' => 2],
            ['name' => 'تاريخ الإسلام', 'category_group_id' => 2],
            ['name' => 'تاريخ العصور الوسطى', 'category_group_id' => 2],
            ['name' => 'تاريخ الحضارات القديمة', 'category_group_id' => 2],

            // اللغة العربية (ID: 3)
            ['name' => 'النحو', 'category_group_id' => 3],
            ['name' => 'الصرف', 'category_group_id' => 3],
            ['name' => 'البلاغة', 'category_group_id' => 3],
            ['name' => 'اللغة والأدب العربي', 'category_group_id' => 3],
            ['name' => 'المعاجم اللغوية', 'category_group_id' => 3],

            // العلوم الاجتماعية (ID: 4)
            ['name' => 'الأنثروبولوجيا', 'category_group_id' => 4],
            ['name' => 'الاجتماع', 'category_group_id' => 4],
            ['name' => 'علم الاجتماع السياسي', 'category_group_id' => 4],
            ['name' => 'علم الثقافة', 'category_group_id' => 4],

            // القانون (ID: 5)
            ['name' => 'القانون المدني', 'category_group_id' => 5],
            ['name' => 'القانون الجنائي', 'category_group_id' => 5],
            ['name' => 'قانون العمل', 'category_group_id' => 5],
            ['name' => 'قانون الأحوال الشخصية', 'category_group_id' => 5],

            // الفلسفة (ID: 6)
            ['name' => 'المنطق', 'category_group_id' => 6],
            ['name' => 'الفلاسفة اليونانيون', 'category_group_id' => 6],
            ['name' => 'الفكر الفلسفي الحديث', 'category_group_id' => 6],
            ['name' => 'الأخلاق والفلسفة', 'category_group_id' => 6],

            // علم النفس (ID: 7)
            ['name' => 'علم النفس الاجتماعي', 'category_group_id' => 7],
            ['name' => 'علم النفس التطوري', 'category_group_id' => 7],
            ['name' => 'علم النفس الإكلينيكي', 'category_group_id' => 7],
            ['name' => 'علم النفس التربوي', 'category_group_id' => 7],

            // التنمية البشرية (ID: 8)
            ['name' => 'تطوير الذات', 'category_group_id' => 8],
            ['name' => 'مهارات القيادة', 'category_group_id' => 8],
            ['name' => 'إدارة الوقت', 'category_group_id' => 8],
            ['name' => 'التفكير الإيجابي', 'category_group_id' => 8],

            // الإدارة (ID: 9)
            ['name' => 'إدارة المشاريع', 'category_group_id' => 9],
            ['name' => 'إدارة الموارد البشرية', 'category_group_id' => 9],
            ['name' => 'إدارة الجودة', 'category_group_id' => 9],
            ['name' => 'إدارة العمليات', 'category_group_id' => 9],

            // الاقتصاد (ID: 10)
            ['name' => 'الاقتصاد الكلي', 'category_group_id' => 10],
            ['name' => 'الاقتصاد الجزئي', 'category_group_id' => 10],
            ['name' => 'الاقتصاد الدولي', 'category_group_id' => 10],
            ['name' => 'التمويل الشخصي', 'category_group_id' => 10],

            // السياسة (ID: 11)
            ['name' => 'العلاقات الدولية', 'category_group_id' => 11],
            ['name' => 'الديبلوماسية', 'category_group_id' => 11],
            ['name' => 'النظم السياسية', 'category_group_id' => 11],
            ['name' => 'السياسات العامة', 'category_group_id' => 11],

            // الجغرافيا (ID: 12)
            ['name' => 'الجغرافيا الطبيعية', 'category_group_id' => 12],
            ['name' => 'الجغرافيا البشرية', 'category_group_id' => 12],
            ['name' => 'الخرائط والتخطيط العمراني', 'category_group_id' => 12],
            ['name' => 'الجغرافيا الاقتصادية', 'category_group_id' => 12],

            // العلوم الطبيعية (ID: 13)
            ['name' => 'الكيمياء', 'category_group_id' => 13],
            ['name' => 'الفيزياء', 'category_group_id' => 13],
            ['name' => 'الرياضيات', 'category_group_id' => 13],
            ['name' => 'علم الأحياء', 'category_group_id' => 13],

            // الهندسة (ID: 14)
            ['name' => 'الهندسة المدنية', 'category_group_id' => 14],
            ['name' => 'الهندسة الكهربائية', 'category_group_id' => 14],
            ['name' => 'الهندسة الميكانيكية', 'category_group_id' => 14],
            ['name' => 'الهندسة البرمجية', 'category_group_id' => 14],

            // الطب (ID: 15)
            ['name' => 'تشريح الإنسان', 'category_group_id' => 15],
            ['name' => 'الأمراض المعدية', 'category_group_id' => 15],
            ['name' => 'الصيدلة', 'category_group_id' => 15],
            ['name' => 'الطب البديل', 'category_group_id' => 15],

            // الأدب (ID: 16)
            ['name' => 'الشعر العربي', 'category_group_id' => 16],
            ['name' => 'القصة القصيرة', 'category_group_id' => 16],
            ['name' => 'الرواية', 'category_group_id' => 16],
            ['name' => 'الأدب العالمي', 'category_group_id' => 16],

            // البرمجة (ID: 17)
            ['name' => 'تطوير الويب', 'category_group_id' => 17],
            ['name' => 'الذكاء الاصطناعي', 'category_group_id' => 17],
            ['name' => 'التطبيقات المحمولة', 'category_group_id' => 17],
            ['name' => 'البيانات الضخمة', 'category_group_id' => 17],

            // الفنون (ID: 18)
            ['name' => 'الرسم', 'category_group_id' => 18],
            ['name' => 'النحت', 'category_group_id' => 18],
            ['name' => 'التصوير الضوئي', 'category_group_id' => 18],
            ['name' => 'الموسيقى', 'category_group_id' => 18],
            ['name' => 'المسرح', 'category_group_id' => 18],
            ['name' => 'السينما', 'category_group_id' => 18],

            // التكنولوجيا (ID: 19)
            ['name' => 'ذكاء اصطناعي', 'category_group_id' => 19],
            ['name' => 'الروبوتات', 'category_group_id' => 19],
            ['name' => 'الأمن السيبراني', 'category_group_id' => 19],
            ['name' => 'الحوسبة السحابية', 'category_group_id' => 19],
            ['name' => 'الإنترنت إنترنت الأشياء', 'category_group_id' => 19],

            // الرياضة (ID: 20)
            ['name' => 'كرة القدم', 'category_group_id' => 20],
            ['name' => 'كرة السلة', 'category_group_id' => 20],
            ['name' => 'رفع الأثقال', 'category_group_id' => 20],
            ['name' => 'ألعاب القوى', 'category_group_id' => 20],
            ['name' => 'سباقات السيارات', 'category_group_id' => 20],

            // البيئة (ID: 21)
            ['name' => 'علم المناخ', 'category_group_id' => 21],
            ['name' => 'التنوع البيولوجي', 'category_group_id' => 21],
            ['name' => 'إدارة النفايات', 'category_group_id' => 21],
            ['name' => 'الاستدامة البيئية', 'category_group_id' => 21],
            ['name' => 'الطاقة المتجددة', 'category_group_id' => 21],

            // الصحة العامة (ID: 22)
            ['name' => 'الرعاية الصحية', 'category_group_id' => 22],
            ['name' => 'التغذية', 'category_group_id' => 22],
            ['name' => 'الصحة النفسية', 'category_group_id' => 22],
            ['name' => 'الوقاية من الأمراض', 'category_group_id' => 22],
            ['name' => 'الصحة العامة للأطفال', 'category_group_id' => 22],

            // السيرة الذاتية (ID: 23)
            ['name' => 'سير شخصيات عالمية', 'category_group_id' => 23],
            ['name' => 'سير الشخصيات التاريخية', 'category_group_id' => 23],
            ['name' => 'سير رواد الأعمال', 'category_group_id' => 23],

            // الطهي (ID: 24)
            ['name' => 'وصفات الشرق الأوسط', 'category_group_id' => 24],
            ['name' => 'الطهي العالمي', 'category_group_id' => 24],
            ['name' => 'الحلويات', 'category_group_id' => 24],
            ['name' => 'الطهي الصحي', 'category_group_id' => 24],

            // العلوم الدينية المقارنة (ID: 25)
            ['name' => 'المذاهب الدينية', 'category_group_id' => 25],
            ['name' => 'الدراسات المقارنة للديانات', 'category_group_id' => 25],
            ['name' => 'الدين والمجتمع', 'category_group_id' => 25],
            ['name' => 'التاريخ الديني', 'category_group_id' => 25],
        ]);
    }
}
