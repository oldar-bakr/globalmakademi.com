(function () {
  'use strict';

  var STORAGE_KEY = 'gm_lang';
  var DEFAULT_LANG = 'en';
  var LANGUAGES = [
    { code: 'en', label: 'English',  short: 'EN', dir: 'ltr' },
    { code: 'tr', label: 'Türkçe',   short: 'TR', dir: 'ltr' },
    { code: 'ar', label: 'العربية',  short: 'AR', dir: 'rtl' }
  ];
  var SUPPORTED = LANGUAGES.map(function (l) { return l.code; });

  // Translations: key = trimmed English source text.
  // Only UI chrome / static copy is translated. Dynamic DB-backed course
  // titles & descriptions stay in their authored language.
  var DICT = {
    tr: {
      // Nav / header
      'Home': 'Ana Sayfa',
      'About': 'Hakkımızda',
      'Programs': 'Programlar',
      'Gallery': 'Galeri',
      'Contact': 'İletişim',
      'Inquire Now': 'Hemen Sorun',
      'Skip to main content': 'Ana içeriğe geç',
      'Toggle menu': 'Menüyü aç/kapat',
      'Language': 'Dil',

      // Hero
      'Premium Industrial Training Partner': 'Birinci Sınıf Endüstriyel Eğitim Ortağı',
      'Training': 'Eğitim',
      '& Consultancy': 've Danışmanlık',
      'Uniting industry expertise, visionary leadership, and world-class academics on a global stage':
        'Sektör uzmanlığını, vizyoner liderliği ve dünya standartlarındaki akademisyenleri küresel sahnede buluşturuyoruz',
      'Explore Programs': 'Programları Keşfet',
      'Contact Us': 'Bize Ulaşın',

      // Trust bar
      'Trusted by Industry Leaders': 'Sektör Liderlerinin Tercihi',

      // Stats
      'Trainees Trained': 'Eğitilen Katılımcı',
      'Professional Trainers': 'Profesyonel Eğitmen',
      'Entities Served': 'Hizmet Verilen Kurum',
      'Countries': 'Ülke',
      'Specialized Programs': 'Uzmanlaşmış Program',
      'Trainees': 'Katılımcı',
      'Trainers': 'Eğitmen',
      'Entities': 'Kurum',

      // Mission / Vision
      'Our Mission': 'Misyonumuz',
      'Our Vision': 'Vizyonumuz',
      'At Makademi Training & Consultancy Ltd, our mission is to be a beacon of excellence in numerous industries across the Middle East, Africa, and Asia. We are dedicated to delivering innovative and sustainable solutions that meet the energy needs of today while safeguarding the environmental integrity for future generations. Guided by a commitment to safety, integrity, and client satisfaction.':
        'Makademi Training & Consultancy Ltd olarak misyonumuz, Orta Doğu, Afrika ve Asya genelinde pek çok sektörde mükemmelliğin öncüsü olmaktır. Bugünün enerji ihtiyaçlarını karşılarken gelecek nesiller için çevresel bütünlüğü koruyan yenilikçi ve sürdürülebilir çözümler sunmaya kararlıyız. Güvenlik, dürüstlük ve müşteri memnuniyetine olan bağlılığımız bize yön verir.',
      'Our vision is to be a leading force in advancing environmentally responsible practices within the oil and gas industry. Through cutting-edge technologies, unwavering commitment to safety, and a culture of continuous innovation, we aim to be the preferred partner for clients seeking reliable, efficient, and sustainable energy solutions.':
        'Vizyonumuz, petrol ve gaz sektöründe çevreye duyarlı uygulamaların geliştirilmesinde öncü bir güç olmaktır. İleri teknolojiler, güvenlikten ödün vermeyen yaklaşımımız ve sürekli yeniliği esas alan kültürümüzle güvenilir, verimli ve sürdürülebilir enerji çözümleri arayan müşterilerin tercih ettiği iş ortağı olmayı hedefliyoruz.',
      'At Global Makademi, our mission is to be a beacon of excellence in the oil and gas industry within the Mediterranean region. We are dedicated to delivering innovative and sustainable solutions that meet the energy needs of today while safeguarding the environmental integrity for future generations. Guided by a commitment to safety, integrity, and client satisfaction.':
        'Global Makademi olarak misyonumuz, Akdeniz bölgesindeki petrol ve gaz sektöründe mükemmelliğin öncüsü olmaktır. Bugünün enerji ihtiyaçlarını karşılarken gelecek nesiller için çevresel bütünlüğü koruyan yenilikçi ve sürdürülebilir çözümler sunmaya kararlıyız. Güvenlik, dürüstlük ve müşteri memnuniyetine olan bağlılığımız bize yön verir.',

      // Services
      'What We Do': 'Ne Yapıyoruz',
      'Oil & Gas': 'Petrol ve Gaz',
      'Specialist training and competency development for professionals operating in upstream, midstream, and downstream energy environments.':
        'Yukarı, orta ve aşağı akış enerji ortamlarında çalışan profesyoneller için uzman eğitim ve yetkinlik geliştirme.',
      'AI': 'Yapay Zekâ',
      'Practical, industry-focused programmes that equip teams with the skills to harness artificial intelligence in real-world business operations.':
        'Ekiplere yapay zekâyı gerçek iş süreçlerinde kullanma becerisi kazandıran, sektöre yönelik uygulamalı programlar.',
      'Telecommunications': 'Telekomünikasyon',
      'Technical and operational training designed for the demands of modern telecoms infrastructure, networks, and service delivery.':
        'Modern telekom altyapısı, ağlar ve hizmet sunumunun gereksinimlerine yönelik teknik ve operasyonel eğitim.',
      'Leadership and Management': 'Liderlik ve Yönetim',
      'Structured development programmes that build confident leaders and effective managers across every level of an organisation.':
        'Organizasyonun her seviyesinde güvenli liderler ve etkili yöneticiler yetiştiren yapılandırılmış gelişim programları.',
      'Learn More': 'Daha Fazla',

      // Categories
      'Our Training Categories': 'Eğitim Kategorilerimiz',
      'The 10 official program families that make up our complete portfolio. Tap any category to view the courses.':
        'Portföyümüzün tamamını oluşturan 10 resmi program ailesi. Kursları görmek için herhangi bir kategoriye dokunun.',
      'Engineering & Technical': 'Mühendislik ve Teknik',
      'Maintenance & Production': 'Bakım ve Üretim',
      'Banking & Finance': 'Bankacılık ve Finans',
      'Telecom & Digital': 'Telekom ve Dijital',
      'Fire Safety & Emergency': 'Yangın Güvenliği ve Acil Durum',
      'Health, Safety & Environment': 'Sağlık, Güvenlik ve Çevre',
      'Corrosion & Integrity': 'Korozyon ve Bütünlük',
      'Management & Leadership': 'Yönetim ve Liderlik',
      'Finance & Accounting': 'Finans ve Muhasebe',
      'High-Value Programs': 'Yüksek Değerli Programlar',

      // Gallery section
      'Our Facilities & Events': 'Tesislerimiz ve Etkinliklerimiz',
      'A glimpse into our world-class training facilities and the events that bring industry professionals together.':
        'Dünya standartlarındaki eğitim tesislerimize ve sektör profesyonellerini bir araya getiren etkinliklerimize bir bakış.',
      'Training Gallery': 'Eğitim Galerisi',
      'A look inside our programs — live-fire drills, classroom sessions, and on-site industrial training around the world.':
        'Programlarımızın içinden bir bakış — canlı yangın tatbikatları, sınıf oturumları ve dünya genelinde sahada endüstriyel eğitim.',
      'Gallery is being updated. Please check back soon.': 'Galeri güncelleniyor. Lütfen kısa süre sonra tekrar kontrol edin.',
      'Want to see one of our programs in person?': 'Programlarımızdan birini yerinde görmek ister misiniz?',
      'We host on-site visits and tailor corporate programs at facilities across Türkiye, Libya, and the wider region.':
        'Türkiye, Libya ve çevre bölgedeki tesislerde saha ziyaretleri düzenliyor ve kurumlara özel programlar hazırlıyoruz.',
      'Get in Touch': 'Bize Ulaşın',

      // Featured
      'Featured Programs': 'Öne Çıkan Programlar',
      'High-impact professional training courses selected for current industry demands.':
        'Güncel sektör ihtiyaçları için seçilmiş, yüksek etkili profesyonel eğitim kursları.',
      'View All 100+ Programs': 'Tüm 100+ Programı Görüntüle',
      '2 Weeks': '2 Hafta',
      '1 Week': '1 Hafta',
      '10 Days': '10 Gün',
      'View Details': 'Detayları Görüntüle',

      // CTA
      'Ready to Elevate Your Team?': 'Ekibinizi Bir Üst Seviyeye Taşımaya Hazır mısınız?',
      'Get in touch with our training consultants to develop a customized program for your organization.':
        'Kurumunuza özel bir program geliştirmek için eğitim danışmanlarımızla iletişime geçin.',

      // Footer
      'Quick Links': 'Hızlı Bağlantılar',
      'Program Categories': 'Program Kategorileri',
      'Partners': 'Ortaklar',
      'Excellence in Industrial Makademi Training & Consultancy Ltd serving the Mediterranean region and beyond.':
        'Akdeniz bölgesi ve ötesinde hizmet veren Endüstriyel Makademi Training & Consultancy Ltd ile mükemmellik.',
      '© 2026 Makademi Training & Consultancy Ltd. All rights reserved.':
        '© 2026 Makademi Training & Consultancy Ltd. Tüm hakları saklıdır.',

      // About
      'About Global Makademi': 'Global Makademi Hakkında',
      'Excellence in Industrial Makademi Training & Consultancy Ltd for the modern energy sector.':
        'Modern enerji sektörü için Endüstriyel Makademi Training & Consultancy Ltd ile mükemmellik.',
      'Global Accreditations': 'Küresel Akreditasyonlar',
      'JOIFF Accredited': 'JOIFF Akreditasyonu',
      'Internationally recognized industrial firefighting and emergency response training provider.':
        'Uluslararası alanda tanınan endüstriyel yangın söndürme ve acil müdahale eğitimi sağlayıcısı.',
      'ISO Standard Compliant': 'ISO Standartlarına Uygun',
      'Operating under strict international quality and educational standards.':
        'Sıkı uluslararası kalite ve eğitim standartlarına uygun olarak faaliyet gösteriyoruz.',
      'Ontario Tech × Brilliant Catalyst': 'Ontario Tech × Brilliant Catalyst',
      'Partnered with Ontario Tech University and Brilliant Catalyst to deliver world-class academic programs.':
        'Dünya standartlarında akademik programlar sunmak için Ontario Tech University ve Brilliant Catalyst ile iş birliği.',
      'Our Clients': 'Müşterilerimiz',
      'Global Makademi proudly serves leading national oil companies and energy enterprises across Libya and the wider Mediterranean region.':
        'Global Makademi, Libya ve geniş Akdeniz bölgesindeki önde gelen ulusal petrol şirketleri ile enerji kuruluşlarına gururla hizmet veriyor.',
      'Strategic Partners': 'Stratejik Ortaklar',
      'Academic & Industry Partners': 'Akademik ve Sektörel Ortaklar',
      'Collaborating with world-class universities and accreditation bodies to deliver internationally recognized training.':
        'Uluslararası alanda tanınan eğitim sunmak için dünya çapında üniversiteler ve akreditasyon kuruluşlarıyla iş birliği yapıyoruz.',

      // Contact
      'Contact our training consultants to request a customized program, inquire about schedules, or discuss corporate partnerships.':
        'Kurumunuza özel bir program talep etmek, takvim bilgisi almak veya kurumsal iş birliklerini görüşmek için eğitim danışmanlarımızla iletişime geçin.',
      'Contact Information': 'İletişim Bilgileri',
      'Email': 'E-posta',
      'Headquarters': 'Genel Merkez',
      'Beyoğlu, İstanbul, Türkiye': 'Beyoğlu, İstanbul, Türkiye',
      'Postal Code: 34435': 'Posta Kodu: 34435',
      'Global delivery — on-site available worldwide': 'Küresel hizmet — dünya genelinde sahada eğitim',
      'Phone': 'Telefon',
      'Office (Tel)': 'Ofis (Tel)',
      'Company landline': 'Şirket sabit hattı',
      'Fax': 'Faks',
      'Operating Regions': 'Faaliyet Bölgeleri',
      'Türkiye · Libya · UAE': 'Türkiye · Libya · BAE',
      'Middle East & North Africa': 'Orta Doğu ve Kuzey Afrika',
      'Corporate Training Requests': 'Kurumsal Eğitim Talepleri',
      'Need training delivered at your facility? Our global team deploys worldwide.':
        'Eğitimin tesisinizde verilmesini ister misiniz? Küresel ekibimiz dünyanın her yerine gidiyor.',
      'Request Proposal': 'Teklif İsteyin',
      'Send us a Message': 'Bize Mesaj Gönderin',
      'Full Name *': 'Ad Soyad *',
      'Company / Organization *': 'Şirket / Kurum *',
      'Business Email *': 'Kurumsal E-posta *',
      'Phone Number': 'Telefon Numarası',
      'Industry': 'Sektör',
      'Energy': 'Enerji',
      'Automotive': 'Otomotiv',
      'HSE / Safety': 'İSG / Güvenlik',
      'Government': 'Kamu',
      'Other': 'Diğer',
      'Subject': 'Konu',
      'Message *': 'Mesaj *',
      'Please provide details about your training requirements...': 'Lütfen eğitim ihtiyaçlarınızla ilgili detayları paylaşın...',
      'Submit Inquiry': 'Talebi Gönder',
      'Sending...': 'Gönderiliyor...',
      'Inquiry Sent Successfully': 'Talebiniz Başarıyla Gönderildi',
      'Thank you for contacting Global Makademi. One of our training consultants will respond within 24 hours.':
        'Global Makademi ile iletişime geçtiğiniz için teşekkür ederiz. Eğitim danışmanlarımızdan biri 24 saat içinde size dönüş yapacaktır.',
      'Send Another Message': 'Yeni Bir Mesaj Gönder',

      // Programs
      'Training Programs': 'Eğitim Programları',
      'Categories': 'Kategoriler',
      'All': 'Tümü',
      'No programs found': 'Program bulunamadı',
      'Try adjusting your search terms or selecting a different category.':
        'Arama terimlerinizi değiştirmeyi veya farklı bir kategori seçmeyi deneyin.',
      'Clear Filters': 'Filtreleri Temizle',
      'Search programs by title or keyword...': 'Programları başlık ya da anahtar kelimeye göre arayın...'
    },

    ar: {
      // Nav / header
      'Home': 'الرئيسية',
      'About': 'من نحن',
      'Programs': 'البرامج',
      'Gallery': 'المعرض',
      'Contact': 'تواصل معنا',
      'Inquire Now': 'استفسر الآن',
      'Skip to main content': 'الانتقال إلى المحتوى الرئيسي',
      'Toggle menu': 'فتح/إغلاق القائمة',
      'Language': 'اللغة',

      // Hero
      'Premium Industrial Training Partner': 'شريك التدريب الصناعي المتميز',
      'Training': 'التدريب',
      '& Consultancy': 'والاستشارات',
      'Uniting industry expertise, visionary leadership, and world-class academics on a global stage':
        'نجمع بين الخبرة الصناعية والقيادة الرؤيوية والأكاديميين على مستوى عالمي على منصة دولية',
      'Explore Programs': 'استكشف البرامج',
      'Contact Us': 'تواصل معنا',

      // Trust bar
      'Trusted by Industry Leaders': 'موضع ثقة قادة الصناعة',

      // Stats
      'Trainees Trained': 'متدرب تم تدريبهم',
      'Professional Trainers': 'مدرب محترف',
      'Entities Served': 'جهة تم خدمتها',
      'Countries': 'دولة',
      'Specialized Programs': 'برنامج متخصص',
      'Trainees': 'المتدربون',
      'Trainers': 'المدربون',
      'Entities': 'الجهات',

      // Mission / Vision
      'Our Mission': 'مهمتنا',
      'Our Vision': 'رؤيتنا',
      'At Makademi Training & Consultancy Ltd, our mission is to be a beacon of excellence in numerous industries across the Middle East, Africa, and Asia. We are dedicated to delivering innovative and sustainable solutions that meet the energy needs of today while safeguarding the environmental integrity for future generations. Guided by a commitment to safety, integrity, and client satisfaction.':
        'في شركة Makademi Training & Consultancy Ltd، مهمتنا أن نكون منارة للتميز في العديد من القطاعات في الشرق الأوسط وأفريقيا وآسيا. نحن ملتزمون بتقديم حلول مبتكرة ومستدامة تلبي احتياجات الطاقة اليوم مع الحفاظ على السلامة البيئية للأجيال القادمة، مسترشدين بالتزامنا بالسلامة والنزاهة ورضا العملاء.',
      'Our vision is to be a leading force in advancing environmentally responsible practices within the oil and gas industry. Through cutting-edge technologies, unwavering commitment to safety, and a culture of continuous innovation, we aim to be the preferred partner for clients seeking reliable, efficient, and sustainable energy solutions.':
        'رؤيتنا أن نكون قوة رائدة في تعزيز الممارسات المسؤولة بيئياً داخل قطاع النفط والغاز. من خلال أحدث التقنيات والالتزام الراسخ بالسلامة وثقافة الابتكار المستمر، نسعى لأن نكون الشريك المفضل للعملاء الذين يبحثون عن حلول طاقة موثوقة وفعّالة ومستدامة.',
      'At Global Makademi, our mission is to be a beacon of excellence in the oil and gas industry within the Mediterranean region. We are dedicated to delivering innovative and sustainable solutions that meet the energy needs of today while safeguarding the environmental integrity for future generations. Guided by a commitment to safety, integrity, and client satisfaction.':
        'في Global Makademi، مهمتنا أن نكون منارة للتميز في قطاع النفط والغاز ضمن منطقة البحر المتوسط. نحن ملتزمون بتقديم حلول مبتكرة ومستدامة تلبي احتياجات الطاقة اليوم مع الحفاظ على السلامة البيئية للأجيال القادمة، مسترشدين بالتزامنا بالسلامة والنزاهة ورضا العملاء.',

      // Services
      'What We Do': 'ما الذي نقوم به',
      'Oil & Gas': 'النفط والغاز',
      'Specialist training and competency development for professionals operating in upstream, midstream, and downstream energy environments.':
        'تدريب متخصص وتطوير الكفاءات للمهنيين العاملين في بيئات الطاقة في مراحل الاستكشاف والإنتاج والنقل والتكرير.',
      'AI': 'الذكاء الاصطناعي',
      'Practical, industry-focused programmes that equip teams with the skills to harness artificial intelligence in real-world business operations.':
        'برامج عملية موجهة للقطاع تزود الفرق بالمهارات اللازمة لتسخير الذكاء الاصطناعي في العمليات التجارية الواقعية.',
      'Telecommunications': 'الاتصالات',
      'Technical and operational training designed for the demands of modern telecoms infrastructure, networks, and service delivery.':
        'تدريب فني وتشغيلي مصمم لمتطلبات البنية التحتية للاتصالات الحديثة والشبكات وتقديم الخدمات.',
      'Leadership and Management': 'القيادة والإدارة',
      'Structured development programmes that build confident leaders and effective managers across every level of an organisation.':
        'برامج تطوير منظمة تبني قادة واثقين ومديرين فعّالين على جميع مستويات المنظمة.',
      'Learn More': 'اعرف المزيد',

      // Categories
      'Our Training Categories': 'فئات برامجنا التدريبية',
      'The 10 official program families that make up our complete portfolio. Tap any category to view the courses.':
        'العائلات البرامجية الرسمية العشر التي تشكل محفظتنا الكاملة. اضغط على أي فئة لعرض الدورات.',
      'Engineering & Technical': 'الهندسة والتقنية',
      'Maintenance & Production': 'الصيانة والإنتاج',
      'Banking & Finance': 'المصارف والتمويل',
      'Telecom & Digital': 'الاتصالات والرقمنة',
      'Fire Safety & Emergency': 'السلامة من الحرائق والطوارئ',
      'Health, Safety & Environment': 'الصحة والسلامة والبيئة',
      'Corrosion & Integrity': 'التآكل وسلامة الأصول',
      'Management & Leadership': 'الإدارة والقيادة',
      'Finance & Accounting': 'المالية والمحاسبة',
      'High-Value Programs': 'البرامج عالية القيمة',

      // Gallery section
      'Our Facilities & Events': 'منشآتنا وفعالياتنا',
      'A glimpse into our world-class training facilities and the events that bring industry professionals together.':
        'لمحة عن منشآتنا التدريبية ذات المستوى العالمي والفعاليات التي تجمع متخصصي الصناعة معاً.',
      'Training Gallery': 'معرض التدريب',
      'A look inside our programs — live-fire drills, classroom sessions, and on-site industrial training around the world.':
        'إطلالة من داخل برامجنا — تدريبات إطفاء حية وجلسات صفية وتدريبات صناعية ميدانية حول العالم.',
      'Gallery is being updated. Please check back soon.': 'يتم تحديث المعرض. يرجى العودة قريباً.',
      'Want to see one of our programs in person?': 'هل ترغب في حضور أحد برامجنا على أرض الواقع؟',
      'We host on-site visits and tailor corporate programs at facilities across Türkiye, Libya, and the wider region.':
        'ننظم زيارات ميدانية ونصمم برامج مؤسسية مخصصة في منشآت بتركيا وليبيا والمنطقة الأوسع.',
      'Get in Touch': 'تواصل معنا',

      // Featured
      'Featured Programs': 'البرامج المميزة',
      'High-impact professional training courses selected for current industry demands.':
        'دورات تدريبية مهنية عالية التأثير مختارة لتلبية متطلبات القطاع الحالية.',
      'View All 100+ Programs': 'عرض جميع البرامج (+100)',
      '2 Weeks': 'أسبوعان',
      '1 Week': 'أسبوع',
      '10 Days': '10 أيام',
      'View Details': 'عرض التفاصيل',

      // CTA
      'Ready to Elevate Your Team?': 'هل أنت مستعد للارتقاء بفريقك؟',
      'Get in touch with our training consultants to develop a customized program for your organization.':
        'تواصل مع مستشاري التدريب لدينا لتطوير برنامج مخصص لمؤسستك.',

      // Footer
      'Quick Links': 'روابط سريعة',
      'Program Categories': 'فئات البرامج',
      'Partners': 'الشركاء',
      'Excellence in Industrial Makademi Training & Consultancy Ltd serving the Mediterranean region and beyond.':
        'التميز في Makademi Training & Consultancy Ltd الصناعي بخدمة منطقة البحر المتوسط وما بعدها.',
      '© 2026 Makademi Training & Consultancy Ltd. All rights reserved.':
        '© 2026 Makademi Training & Consultancy Ltd. جميع الحقوق محفوظة.',

      // About
      'About Global Makademi': 'عن Global Makademi',
      'Excellence in Industrial Makademi Training & Consultancy Ltd for the modern energy sector.':
        'التميز في Makademi Training & Consultancy Ltd الصناعي لقطاع الطاقة الحديث.',
      'Global Accreditations': 'الاعتمادات الدولية',
      'JOIFF Accredited': 'معتمد من JOIFF',
      'Internationally recognized industrial firefighting and emergency response training provider.':
        'مزود تدريب معترف به دولياً في إطفاء الحرائق الصناعية والاستجابة للطوارئ.',
      'ISO Standard Compliant': 'متوافق مع معايير ISO',
      'Operating under strict international quality and educational standards.':
        'نعمل وفق معايير دولية صارمة للجودة والتعليم.',
      'Ontario Tech × Brilliant Catalyst': 'Ontario Tech × Brilliant Catalyst',
      'Partnered with Ontario Tech University and Brilliant Catalyst to deliver world-class academic programs.':
        'بالشراكة مع جامعة Ontario Tech وBrilliant Catalyst لتقديم برامج أكاديمية عالمية المستوى.',
      'Our Clients': 'عملاؤنا',
      'Global Makademi proudly serves leading national oil companies and energy enterprises across Libya and the wider Mediterranean region.':
        'تفتخر Global Makademi بخدمة كبرى شركات النفط الوطنية ومؤسسات الطاقة في ليبيا ومنطقة البحر المتوسط الأوسع.',
      'Strategic Partners': 'الشركاء الاستراتيجيون',
      'Academic & Industry Partners': 'الشركاء الأكاديميون والصناعيون',
      'Collaborating with world-class universities and accreditation bodies to deliver internationally recognized training.':
        'بالتعاون مع جامعات وجهات اعتماد عالمية لتقديم تدريب معترف به دولياً.',

      // Contact
      'Contact our training consultants to request a customized program, inquire about schedules, or discuss corporate partnerships.':
        'تواصل مع مستشاري التدريب لدينا لطلب برنامج مخصص، أو الاستفسار عن المواعيد، أو مناقشة الشراكات المؤسسية.',
      'Contact Information': 'معلومات التواصل',
      'Email': 'البريد الإلكتروني',
      'Headquarters': 'المقر الرئيسي',
      'Beyoğlu, İstanbul, Türkiye': 'بي أوغلو، إسطنبول، تركيا',
      'Postal Code: 34435': 'الرمز البريدي: 34435',
      'Global delivery — on-site available worldwide': 'تقديم عالمي — تدريب ميداني متاح حول العالم',
      'Phone': 'الهاتف',
      'Office (Tel)': 'المكتب (هاتف)',
      'Company landline': 'الخط الأرضي للشركة',
      'Fax': 'الفاكس',
      'Operating Regions': 'مناطق العمل',
      'Türkiye · Libya · UAE': 'تركيا · ليبيا · الإمارات',
      'Middle East & North Africa': 'الشرق الأوسط وشمال أفريقيا',
      'Corporate Training Requests': 'طلبات التدريب المؤسسي',
      'Need training delivered at your facility? Our global team deploys worldwide.':
        'هل تحتاج إلى تدريب في منشأتك؟ فريقنا العالمي ينتشر في جميع أنحاء العالم.',
      'Request Proposal': 'طلب عرض',
      'Send us a Message': 'أرسل لنا رسالة',
      'Full Name *': 'الاسم الكامل *',
      'Company / Organization *': 'الشركة / المؤسسة *',
      'Business Email *': 'البريد الإلكتروني للعمل *',
      'Phone Number': 'رقم الهاتف',
      'Industry': 'القطاع',
      'Energy': 'الطاقة',
      'Automotive': 'السيارات',
      'HSE / Safety': 'الصحة والسلامة',
      'Government': 'حكومي',
      'Other': 'أخرى',
      'Subject': 'الموضوع',
      'Message *': 'الرسالة *',
      'Please provide details about your training requirements...': 'يرجى تقديم تفاصيل حول احتياجاتك التدريبية...',
      'Submit Inquiry': 'إرسال الاستفسار',
      'Sending...': 'جاري الإرسال...',
      'Inquiry Sent Successfully': 'تم إرسال الاستفسار بنجاح',
      'Thank you for contacting Global Makademi. One of our training consultants will respond within 24 hours.':
        'شكراً لتواصلك مع Global Makademi. سيقوم أحد مستشاري التدريب لدينا بالرد خلال 24 ساعة.',
      'Send Another Message': 'إرسال رسالة أخرى',

      // Programs
      'Training Programs': 'البرامج التدريبية',
      'Categories': 'الفئات',
      'All': 'الكل',
      'No programs found': 'لم يتم العثور على برامج',
      'Try adjusting your search terms or selecting a different category.':
        'حاول تعديل كلمات البحث أو اختيار فئة مختلفة.',
      'Clear Filters': 'مسح الفلاتر',
      'Search programs by title or keyword...': 'ابحث عن البرامج بالعنوان أو الكلمة المفتاحية...'
    }
  };

  var ATTR_KEYS = ['placeholder', 'title', 'aria-label', 'alt'];

  function langInfo(code) {
    for (var i = 0; i < LANGUAGES.length; i++) {
      if (LANGUAGES[i].code === code) return LANGUAGES[i];
    }
    return LANGUAGES[0];
  }

  function getLang() {
    try {
      var saved = localStorage.getItem(STORAGE_KEY);
      if (saved && SUPPORTED.indexOf(saved) !== -1) return saved;
    } catch (e) {}
    return DEFAULT_LANG;
  }

  function setLang(lang) {
    if (SUPPORTED.indexOf(lang) === -1) lang = DEFAULT_LANG;
    try { localStorage.setItem(STORAGE_KEY, lang); } catch (e) {}
    var info = langInfo(lang);
    document.documentElement.lang = lang;
    document.documentElement.dir = info.dir;
    applyTranslations(lang);
    updateSwitcherUI(lang);
  }

  function translateTextNode(node, dict) {
    var orig = node.nodeValue;
    if (!orig) return;
    var trimmed = orig.replace(/\s+/g, ' ').trim();
    if (!trimmed) return;
    if (!node.__gmOrig) node.__gmOrig = orig;

    if (dict[trimmed]) {
      var leading = orig.match(/^\s*/)[0];
      var trailing = orig.match(/\s*$/)[0];
      node.nodeValue = leading + dict[trimmed] + trailing;
      return;
    }

    var m = trimmed.match(/^Showing\s+(\d+)\s+of\s+(\d+)\s+programs$/);
    if (m) {
      var lang = document.documentElement.lang;
      var phrase;
      if (lang === 'tr') phrase = m[1] + ' / ' + m[2] + ' program gösteriliyor';
      else if (lang === 'ar') phrase = 'عرض ' + m[1] + ' من ' + m[2] + ' برنامج';
      if (phrase) { node.nodeValue = orig.replace(trimmed, phrase); return; }
    }

    m = trimmed.match(/^Explore our comprehensive catalog of (\d+)\+ specialized industrial training courses designed for professionals\.?$/);
    if (m) {
      var lang2 = document.documentElement.lang;
      var phrase2;
      if (lang2 === 'tr') phrase2 = 'Profesyoneller için tasarlanmış ' + m[1] + '+ uzman endüstriyel eğitim kursundan oluşan kapsamlı kataloğumuzu keşfedin.';
      else if (lang2 === 'ar') phrase2 = 'استكشف كتالوجنا الشامل الذي يضم أكثر من ' + m[1] + ' دورة تدريبية صناعية متخصصة مصممة للمهنيين.';
      if (phrase2) { node.nodeValue = orig.replace(trimmed, phrase2); return; }
    }
  }

  function restoreTextNode(node) {
    if (node.__gmOrig != null && node.nodeValue !== node.__gmOrig) {
      node.nodeValue = node.__gmOrig;
    }
  }

  function walkText(root, fn) {
    var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
      acceptNode: function (n) {
        var p = n.parentNode;
        if (!p) return NodeFilter.FILTER_REJECT;
        var tag = p.nodeName;
        if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'NOSCRIPT') return NodeFilter.FILTER_REJECT;
        if (p.closest && p.closest('[data-i18n-skip]')) return NodeFilter.FILTER_REJECT;
        return NodeFilter.FILTER_ACCEPT;
      }
    });
    var n;
    while ((n = walker.nextNode())) fn(n);
  }

  function translateAttributes(dict) {
    ATTR_KEYS.forEach(function (attr) {
      var els = document.querySelectorAll('[' + attr + ']');
      els.forEach(function (el) {
        if (el.closest && el.closest('[data-i18n-skip]')) return;
        var origMap = el.__gmAttrOrig || (el.__gmAttrOrig = {});
        if (origMap[attr] == null) origMap[attr] = el.getAttribute(attr);
        var src = origMap[attr];
        if (src && dict[src.trim()]) {
          el.setAttribute(attr, dict[src.trim()]);
        } else if (src != null) {
          el.setAttribute(attr, src);
        }
      });
    });

    document.querySelectorAll('option').forEach(function (opt) {
      if (opt.__gmOrigText == null) opt.__gmOrigText = opt.textContent;
      var t = opt.__gmOrigText.trim();
      opt.textContent = dict[t] || opt.__gmOrigText;
    });
  }

  function applyTranslations(lang) {
    var dict = DICT[lang] || {};
    if (lang === DEFAULT_LANG || !DICT[lang]) {
      walkText(document.body, restoreTextNode);
      ATTR_KEYS.forEach(function (attr) {
        document.querySelectorAll('[' + attr + ']').forEach(function (el) {
          if (el.__gmAttrOrig && el.__gmAttrOrig[attr] != null) {
            el.setAttribute(attr, el.__gmAttrOrig[attr]);
          }
        });
      });
      document.querySelectorAll('option').forEach(function (opt) {
        if (opt.__gmOrigText != null) opt.textContent = opt.__gmOrigText;
      });
      return;
    }
    walkText(document.body, function (n) { translateTextNode(n, dict); });
    translateAttributes(dict);
  }

  // ---- Dropdown switcher ----

  function makeSwitcher() {
    var wrap = document.createElement('div');
    wrap.className = 'lang-switcher';
    wrap.setAttribute('data-i18n-skip', '');

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'lang-toggle';
    btn.setAttribute('aria-haspopup', 'listbox');
    btn.setAttribute('aria-expanded', 'false');
    btn.setAttribute('aria-label', 'Select language');
    btn.innerHTML =
      '<svg class="lang-globe" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>' +
      '<span class="lang-current">EN</span>' +
      '<svg class="lang-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';

    var menu = document.createElement('ul');
    menu.className = 'lang-menu';
    menu.setAttribute('role', 'listbox');
    menu.hidden = true;

    LANGUAGES.forEach(function (l) {
      var li = document.createElement('li');
      li.setAttribute('role', 'option');
      li.setAttribute('data-lang', l.code);
      li.innerHTML =
        '<span class="lang-code">' + l.short + '</span>' +
        '<span class="lang-label">' + l.label + '</span>';
      menu.appendChild(li);
    });

    function close() {
      menu.hidden = true;
      btn.setAttribute('aria-expanded', 'false');
    }
    function open() {
      menu.hidden = false;
      btn.setAttribute('aria-expanded', 'true');
    }

    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      if (menu.hidden) open(); else close();
    });
    menu.addEventListener('click', function (e) {
      var li = e.target.closest('li[data-lang]');
      if (!li) return;
      var code = li.getAttribute('data-lang');
      setLang(code);
      close();
    });
    document.addEventListener('click', function (e) {
      if (!wrap.contains(e.target)) close();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !menu.hidden) close();
    });

    wrap.appendChild(btn);
    wrap.appendChild(menu);
    return wrap;
  }

  function updateSwitcherUI(lang) {
    var info = langInfo(lang);
    document.querySelectorAll('.lang-switcher').forEach(function (sw) {
      var label = sw.querySelector('.lang-current');
      if (label) label.textContent = info.short;
      sw.querySelectorAll('.lang-menu li').forEach(function (li) {
        if (li.getAttribute('data-lang') === lang) li.classList.add('selected');
        else li.classList.remove('selected');
      });
    });
  }

  function injectStyles() {
    if (document.getElementById('gm-i18n-styles')) return;
    var s = document.createElement('style');
    s.id = 'gm-i18n-styles';
    s.textContent = [
      '.lang-switcher{position:relative;display:inline-flex;align-items:center;margin-right:0.75rem;font-size:0.8125rem;font-weight:600;}',
      '.lang-toggle{display:inline-flex;align-items:center;gap:0.4rem;background:transparent;border:1px solid var(--slate-200,#e2e8f0);border-radius:9999px;padding:0.35rem 0.7rem;color:var(--slate-700,#334155);cursor:pointer;font:inherit;line-height:1;transition:border-color .15s,color .15s,background .15s;}',
      '.lang-toggle:hover{border-color:var(--gold,#d4af37);color:var(--navy,#0f172a);}',
      '.lang-toggle[aria-expanded="true"]{border-color:var(--gold,#d4af37);color:var(--navy,#0f172a);background:rgba(212,175,55,0.08);}',
      '.lang-toggle .lang-globe{flex-shrink:0;}',
      '.lang-toggle .lang-current{letter-spacing:0.05em;}',
      '.lang-toggle .lang-chevron{transition:transform .2s;flex-shrink:0;opacity:0.7;}',
      '.lang-toggle[aria-expanded="true"] .lang-chevron{transform:rotate(180deg);}',
      '.lang-menu{position:absolute;top:calc(100% + 0.4rem);right:0;left:auto;list-style:none;margin:0;padding:0.35rem;min-width:10rem;background:#fff;border:1px solid var(--slate-200,#e2e8f0);border-radius:0.5rem;box-shadow:0 10px 25px -5px rgba(15,23,42,0.15),0 4px 10px -3px rgba(15,23,42,0.08);z-index:1000;}',
      '[dir="rtl"] .lang-menu{right:auto;left:0;}',
      '.lang-menu li{display:flex;align-items:center;gap:0.6rem;padding:0.5rem 0.7rem;border-radius:0.375rem;cursor:pointer;color:var(--slate-700,#334155);font-weight:500;transition:background .15s,color .15s;}',
      '.lang-menu li:hover{background:var(--slate-100,#f1f5f9);color:var(--navy,#0f172a);}',
      '.lang-menu li.selected{background:var(--gold,#d4af37);color:var(--navy,#0f172a);}',
      '.lang-menu .lang-code{display:inline-block;min-width:1.6rem;font-weight:700;font-size:0.75rem;letter-spacing:0.05em;opacity:0.85;}',
      '.lang-menu .lang-label{font-size:0.875rem;}',
      '.mobile-nav .lang-switcher{margin:0.75rem 1rem;display:flex;justify-content:center;}',
      '.mobile-nav .lang-toggle{font-size:1rem;padding:0.5rem 0.9rem;}',
      '@media (max-width: 900px){.desktop-nav .lang-switcher{display:none;}}',
      // RTL polish: keep nav links visually consistent
      '[dir="rtl"] body{text-align:right;}',
      '[dir="rtl"] .nav-links{flex-direction:row-reverse;}'
    ].join('');
    document.head.appendChild(s);
  }

  function injectSwitcher() {
    var desktop = document.querySelector('.desktop-nav');
    if (desktop && !desktop.querySelector('.lang-switcher')) {
      var sw = makeSwitcher();
      var inquire = desktop.querySelector('.btn');
      if (inquire) desktop.insertBefore(sw, inquire);
      else desktop.appendChild(sw);
    }
    var mobile = document.getElementById('mobile-nav');
    if (mobile && !mobile.querySelector('.lang-switcher')) {
      var sw2 = makeSwitcher();
      var cta = mobile.querySelector('.mobile-cta');
      if (cta) mobile.insertBefore(sw2, cta);
      else mobile.appendChild(sw2);
    }
  }

  function init() {
    injectStyles();
    injectSwitcher();
    var lang = getLang();
    var info = langInfo(lang);
    document.documentElement.lang = lang;
    document.documentElement.dir = info.dir;
    applyTranslations(lang);
    updateSwitcherUI(lang);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
