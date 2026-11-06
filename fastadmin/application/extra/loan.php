<?php

/**
 * 财政支持(请款)业务配置
 */
return [
    // 年利率(%)
    'interest_rate' => 5,
    // 可选的还款期数(次)
    'terms'         => [3, 6, 12, 24, 36],
    // 默认还款期数
    'default_term'  => 12,
    // 平台收款账户(客户还款时展示的收款卡,后台新建还款单时自动带出)
    'payee'          => [
        'bank_name'    => '测试銀行',
        'branch_name'  => '测试部',
        'account_type' => '普通',
        'account_no'   => '1234567',
        'account_name' => '测试公司',
    ],
    // 协议标题
    'agreement_title' => 'Shaxsiy ma\'lumotlarni qayta ishlash bo\'yicha kelishuv',
    // 协议内容
    'agreement'     => "1-bob: Shaxsiy ma'lumotlardan foydalanish maqsadi\n\n"
        . "1-modda. Shaxsiy ma'lumotlardan foydalanish maqsadi\n"
        . "(1) Kompaniyamiz sizning shaxsiy ma'lumotlaringizdan quyidagi maqsadlarda foydalanadi: 1. kredit baholash uchun; 2. kreditdan keyingi boshqaruv uchun; 3. siz bilan amalga oshirilgan operatsiyalar va to'lovlar hisobini hamda boshqa tegishli ma'lumotlarni saqlash uchun; 4. ushbu ariza bilan bog'liq bildirishnomalarni sizga yuborish uchun; 5. bozor tendensiyalarini tahlil qilish va xizmatlarni yaxshilash uchun; 6. kompaniyamiz va guruh kompaniyalarining faoliyatini hamda sizga zarur bo'lgan turli xatarlarni boshqarish uchun; 7. kompaniyamiz yoki guruh kompaniyalari taqdim etadigan turli mahsulot va xizmatlarni o'rganish va boshqarish uchun; 8. guruh kompaniyalarimizning moliyaviy mahsulot va xizmatlarini tanishtirish uchun.\n"
        . "(2) Yuqoridagilardan tashqari, reklama samaradorligini o'lchash uchun kompaniyamiz uchinchi tomon vositalari orqali siz ushbu sayt orqali bosgan reklama ma'lumotlarini (bosilgan sana va reklama joylashtiruvchini o'z ichiga olgan holda) olishi mumkin.\n\n"
        . "2-bob: Shaxsiy ma'lumotlarni uchinchi shaxslarga taqdim etish\n\n"
        . "(1) Quyidagi holatlardan tashqari, kompaniyamiz sizning shaxsiy ma'lumotlaringizni kompaniyamizdan boshqa hech qanday uchinchi shaxsga taqdim etmaydi: 1. sizning roziligingiz olinganda; 2. qonun hujjatlariga muvofiq; 3. boshqa shaxslarning hayoti, sog'lig'i yoki mulkini himoya qilish uchun zarur bo'lib, shaxsning roziligini olish qiyin bo'lganda.\n"
        . "(2) Kompaniyamiz shaxsiy ma'lumotlarni biznes topshiruvchiga taqdim etishi mumkin, topshiruvchi ularni faqat kompaniyamiz belgilagan maqsadlar doirasida ishlatadi.\n\n"
        . "3-bob: Boshqa shartlar\n\n"
        . "(1) Kompaniyamiz shaxsiy ma'lumotlarning oshkor bo'lishi, yo'qolishi yoki buzilishining oldini olish uchun zarur xavfsizlik choralarini ko'radi.\n"
        . "(2) Siz istalgan vaqtda mijozlarga xizmat ko'rsatish orqali shaxsiy ma'lumotlaringizni so'rash, tuzatish yoki ulardan foydalanishni to'xtatishni talab qilishingiz mumkin.\n"
        . "(3) 18 yoshga to'lmagan arizachilar ariza topshirishdan oldin qonuniy vasiysining roziligini olishlari shart.",
];
