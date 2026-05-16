<?php

return [
    // ═══════════════════════════════════════════
    // Danh mục (category)
    // ═══════════════════════════════════════════
    'categories' => [
        'huong-dan'  => ['slug' => 'huong-dan',  'name' => 'Hướng dẫn',  'color' => '#d97b9d'],
        'mau-dan'    => ['slug' => 'mau-dan',    'name' => 'Mẫu đan',    'color' => '#b15e1f'],
        'bao-quan'   => ['slug' => 'bao-quan',   'name' => 'Bảo quản',   'color' => '#2c5580'],
        'cam-hung'   => ['slug' => 'cam-hung',   'name' => 'Cảm hứng',   'color' => '#3d7a52'],
        'cau-chuyen' => ['slug' => 'cau-chuyen', 'name' => 'Câu chuyện', 'color' => '#5b4ba5'],
    ],

    // ═══════════════════════════════════════════
    // Bài viết
    // ═══════════════════════════════════════════
    'posts' => [
        [
            'slug'      => 'bat-dau-voi-moc-len-cho-nguoi-moi',
            'title'     => 'Bắt đầu với móc len: hướng dẫn chi tiết cho người mới',
            'excerpt'   => 'Chưa từng cầm kim móc? Bài này sẽ dẫn bạn đi từ cách chọn len, cầm kim đúng, đến mũi móc đầu tiên — tất cả trong 15 phút.',
            'cover'     => '/images/1.jpg',
            'category'  => 'huong-dan',
            'author'    => 'Minh Anh',
            'date'      => '2026-04-10',
            'read_time' => 8,
            'featured'  => true,
            'tags'      => ['người mới', 'kim móc', 'cơ bản'],
            'sections'  => [
                [
                    'heading' => 'Chuẩn bị dụng cụ',
                    'body'    => '<p>Bạn chỉ cần 3 thứ để bắt đầu: <strong>1 cuộn len cotton pastel size vừa (8-ply)</strong>, <strong>1 kim móc số 4mm</strong>, và <strong>1 cái kéo nhỏ</strong>. Len cotton cho người mới là lựa chọn tuyệt vời vì nó ít xù, dễ nhìn mũi, và màu pastel sẽ làm bạn đỡ nản khi sai.</p><p>Tránh chọn len mohair hoặc len quá mảnh ngay lần đầu - sợi rối và khó tháo sẽ khiến bạn bỏ cuộc trong 10 phút.</p>',
                ],
                [
                    'heading' => 'Cách cầm kim và len',
                    'body'    => '<p>Có hai cách cầm phổ biến: <em>cầm như cây bút (pen grip)</em> phù hợp cho người quen viết tay, và <em>cầm như con dao (knife grip)</em> phù hợp nếu bạn muốn móc nhanh. Không có cách đúng sai - thử cả hai trong 2 phút đầu, cái nào thấy tay không mỏi thì theo.</p><p>Len quấn quanh ngón trỏ trái (nếu bạn thuận tay phải), kéo nhẹ để sợi có độ căng vừa đủ - không quá chặt (mũi sẽ cứng), không quá lỏng (mũi sẽ lỏng lẻo).</p>',
                ],
                [
                    'heading' => 'Mũi bính (chain stitch) - mũi đầu tiên',
                    'body'    => '<p>Mũi bính là nền tảng của mọi mẫu móc. Cách làm:</p><ol><li>Tạo một vòng thòng lọng trên kim.</li><li>Xuyên kim qua vòng, móc sợi len lên.</li><li>Kéo sợi len qua vòng cũ - bạn vừa tạo được mũi bính đầu tiên.</li><li>Lặp lại 20 lần để quen động tác.</li></ol><p>Đừng ngạc nhiên nếu 10 mũi đầu tiên trông như mì gói - đây là trải nghiệm chung của tất cả mọi người. Sau 20-30 mũi tay sẽ tự điều chỉnh.</p>',
                ],
                [
                    'heading' => 'Bài tập đầu tiên: khăn tay len',
                    'body'    => '<p>Khi đã móc được mũi bính đều, hãy thử làm một chiếc khăn tay đơn giản: móc 40 mũi bính, sau đó móc 20 hàng mũi đơn (single crochet). Kết quả sẽ là một miếng len hình chữ nhật ~15×15cm - dùng để lau ly cà phê hoặc làm miếng lót cốc.</p><p>Đây là dự án hoàn hảo vì: ngắn, dùng được ngay, và nhìn thành phẩm là động lực để làm tiếp cái lớn hơn.</p>',
                ],
            ],
        ],
        [
            'slug'      => 'chon-len-cho-tung-loai-project',
            'title'     => 'Chọn đúng loại len cho từng dự án: đừng đoán, hãy biết',
            'excerpt'   => 'Len cotton, len wool, len acrylic, len mohair - mỗi loại có đặc tính riêng. Chọn sai len, thành phẩm sẽ không như ý dù kỹ thuật hoàn hảo.',
            'cover'     => '/images/2.jpg',
            'category'  => 'huong-dan',
            'author'    => 'Thu Hà',
            'date'      => '2026-04-05',
            'read_time' => 6,
            'featured'  => false,
            'tags'      => ['chọn len', 'chất liệu', 'dự án'],
            'sections'  => [
                [
                    'heading' => 'Cotton - len của mùa hè',
                    'body'    => '<p>Cotton là lựa chọn số 1 cho đồ dùng hằng ngày: khăn tay, túi xách, áo mùa hè, amigurumi. Ưu điểm: mát, thấm hút, giặt máy được, không dị ứng. Nhược điểm: ít đàn hồi, nếu đan áo ôm sát sẽ hơi "chùng" sau vài lần giặt.</p>',
                ],
                [
                    'heading' => 'Wool (len cừu) - ấm áp và bền',
                    'body'    => '<p>Len wool là lựa chọn kinh điển cho mùa đông: ấm, đàn hồi tốt, mũi rõ nét. Phù hợp: khăn quàng, mũ, áo len, găng tay. Lưu ý: phải giặt tay nước lạnh — giặt máy sẽ làm len bị xơ (felting) không cứu được.</p>',
                ],
                [
                    'heading' => 'Acrylic - rẻ, bền, dễ bảo quản',
                    'body'    => '<p>Acrylic là len tổng hợp, thường rẻ hơn wool 30-50%. Màu sắc đa dạng rực rỡ, giặt máy thoải mái, không bị co. Phù hợp cho: đồ thực hành, đồ chơi cho trẻ em, blanket. Nhược điểm: kém thoáng khí, không ấm bằng wool thật.</p>',
                ],
                [
                    'heading' => 'Mohair & cashmere - cao cấp, dịp đặc biệt',
                    'body'    => '<p>Mohair (lông dê) cho thành phẩm xù nhẹ, sang trọng. Cashmere (lông dê Kashmir) mềm tuyệt đối nhưng đắt gấp 5-10 lần cotton. Chỉ dùng cho món đồ bạn thật sự trân trọng — khăn quàng sinh nhật, áo cho mẹ, quà cưới bạn thân.</p>',
                ],
            ],
        ],
        [
            'slug'      => 'mau-gau-bong-amigurumi-cho-nguoi-moi',
            'title'     => 'Mẫu gấu bông amigurumi đơn giản - thành phẩm trong 3 buổi tối',
            'excerpt'   => 'Gấu bông mini 12cm, chỉ cần 1 cuộn len cotton pastel và kim 2.5mm. Bao gồm PDF chart và video hướng dẫn từng mũi.',
            'cover'     => '/images/3.jpg',
            'category'  => 'mau-dan',
            'author'    => 'Ngọc Mai',
            'date'      => '2026-03-28',
            'read_time' => 10,
            'featured'  => false,
            'tags'      => ['amigurumi', 'gấu bông', 'mẫu'],
            'sections'  => [
                [
                    'heading' => 'Vật liệu cần chuẩn bị',
                    'body'    => '<ul><li>1 cuộn len cotton (8-ply) màu pastel yêu thích - ~50g.</li><li>Kim móc 2.5mm (cho mũi móc chặt, không lộ bông).</li><li>Bông polyester để nhồi — ~20g.</li><li>Kim khâu len đầu tù.</li><li>Mắt hạt nhựa đường kính 6mm (hoặc len đen nếu tặng bé dưới 3 tuổi).</li><li>Chỉ thêu hồng để thêu mũi và má gấu.</li></ul>',
                ],
                [
                    'heading' => 'Cấu trúc gấu',
                    'body'    => '<p>Gấu gồm 7 chi tiết móc riêng: <strong>đầu</strong> (tròn, rỗng để nhồi), <strong>thân</strong> (hình oval), <strong>4 chân</strong> (hình trụ ngắn), <strong>2 tai</strong> (hình bán nguyệt). Mỗi chi tiết làm xong là nhồi bông ngay trước khi đóng — dễ hơn là để cuối mới nhồi.</p>',
                ],
                [
                    'heading' => 'Chart ký hiệu cơ bản',
                    'body'    => '<ul><li><strong>MR</strong> - Magic ring (vòng ma thuật, khởi đầu cho hình tròn).</li><li><strong>sc</strong> — Single crochet (mũi đơn).</li><li><strong>inc</strong> — Tăng mũi (2 sc vào cùng 1 mũi).</li><li><strong>dec</strong> — Giảm mũi (gộp 2 mũi thành 1).</li><li><strong>sl st</strong> — Slip stitch (mũi chốt).</li></ul><p>Quy ước: con số trước chữ là số lần lặp. Ví dụ <em>"6 sc in MR"</em> = 6 mũi đơn vào vòng ma thuật.</p>',
                ],
                [
                    'heading' => 'Mẹo làm gấu xinh',
                    'body'    => '<p>Hai bí quyết: <strong>nhồi bông đủ nhưng không căng</strong> — căng quá sẽ lộ mũi, ít quá sẽ méo. <strong>Khâu tai hơi nghiêng 15 độ về phía sau</strong> — sẽ trông dễ thương hơn là khâu thẳng đứng. Và đừng quên thêu má hồng nhạt — đây là chi tiết biến con gấu bình thường thành "awww dễ thương quá".</p>',
                ],
            ],
        ],
        [
            'slug'      => 'giat-va-bao-quan-do-len-dung-cach',
            'title'     => 'Giặt và bảo quản đồ len đúng cách - sau 5 năm vẫn như mới',
            'excerpt'   => 'Giặt máy đồ len là cách phá hủy nhanh nhất. Học cách chăm sóc đúng và đồ handmade sẽ đồng hành cùng bạn qua nhiều mùa đông.',
            'cover'     => '/images/4.jpg',
            'category'  => 'bao-quan',
            'author'    => 'Minh Anh',
            'date'      => '2026-03-22',
            'read_time' => 5,
            'featured'  => false,
            'tags'      => ['giặt', 'bảo quản', 'chăm sóc'],
            'sections'  => [
                [
                    'heading' => 'Quy tắc vàng: đọc label',
                    'body'    => '<p>Mỗi loại len có chế độ giặt khác nhau. Cotton thường giặt máy nhẹ, wool phải giặt tay, mohair chỉ giặt khô. Label trên cuộn len luôn có ký hiệu — chụp lại trước khi vứt cuộn rỗng. Làm một file note trên điện thoại để tra cứu.</p>',
                ],
                [
                    'heading' => 'Giặt tay đồ wool - từng bước',
                    'body'    => '<ol><li>Pha nước lạnh (không quá 20°C) với xà phòng dịu - tốt nhất là xà phòng dành riêng cho đồ len (Woolite, Eucalan).</li><li>Ngâm đồ len 10 phút, <strong>KHÔNG VÒ</strong> — chỉ nhẹ nhàng ấn xuống để nước thấm đều.</li><li>Xả 2 lần nước sạch, không vắt xoắn.</li><li>Cuộn vào khăn tắm khô, ấn nhẹ để hút nước.</li><li>Trải phẳng trên lưới phơi, tránh nắng trực tiếp. <strong>KHÔNG TREO</strong> — sẽ bị giãn dài.</li></ol>',
                ],
                [
                    'heading' => 'Bảo quản mùa hè',
                    'body'    => '<p>Đồ len không mặc 6 tháng cần: giặt sạch → phơi khô kỹ → gấp gọn → cất trong túi vải cotton (không dùng túi nhựa kín, sẽ bị ẩm mốc). Cho thêm vài viên long não hoặc lá hương thảo để chống côn trùng — mạch lép ăn len rất nhanh, 1 đêm có thể phá nát 1 chiếc áo.</p>',
                ],
                [
                    'heading' => 'Xử lý khi đồ len bị xù (pilling)',
                    'body'    => '<p>Pilling là hiện tượng tự nhiên của đồ len khi mặc nhiều. Dùng <em>fabric shaver</em> (máy cạo xù, giá ~200-300k) cạo nhẹ trên bề mặt — trong 5 phút đồ len sẽ như mới. Đừng dùng dao lam — sẽ cắt cả sợi len tốt.</p>',
                ],
            ],
        ],
        [
            'slug'      => 'top-5-mau-khan-dong-sang-tao',
            'title'     => '5 mẫu khăn quàng mùa đông ai cũng có thể làm',
            'excerpt'   => 'Từ khăn móc đơn giản chỉ 1 loại mũi đến khăn đan hoa văn cable đẹp mắt — chọn mẫu phù hợp với level của bạn.',
            'cover'     => '/images/5.jpg',
            'category'  => 'mau-dan',
            'author'    => 'Thu Hà',
            'date'      => '2026-03-15',
            'read_time' => 7,
            'featured'  => false,
            'tags'      => ['khăn quàng', 'mùa đông', 'mẫu'],
            'sections'  => [
                [
                    'heading' => '1. Khăn single crochet - level 1',
                    'body'    => '<p>Khăn đơn giản nhất: chỉ dùng mũi single crochet suốt 200 hàng. Độ dài 150cm, rộng 20cm. Dùng len wool chunky size 12-ply, kim 6mm — thành phẩm trong 1 tuần nếu móc đều đặn 1 giờ/ngày. Phù hợp người mới hoàn thành dự án đầu tiên.</p>',
                ],
                [
                    'heading' => '2. Khăn granny stripe - level 2',
                    'body'    => '<p>Kết hợp 3-4 màu len pastel thành từng sọc rộng 5cm. Mỗi sọc là một loại mũi khác nhau (sc, dc, hdc). Thành phẩm có chiều sâu thị giác, mặc không "dẹt" như khăn 1 màu. Kỹ thuật: biết đổi màu khi kết thúc hàng.</p>',
                ],
                [
                    'heading' => '3. Khăn cable (đan kim) - level 3',
                    'body'    => '<p>Mẫu kim đan cổ điển với hoa văn xoắn thừng ở giữa. Cần kim đan thứ 3 (cable needle) để giữ mũi. Thành phẩm rất sang, giống khăn Ireland thủ công. Nếu là lần đầu làm cable, bắt đầu với pattern 4-stitch cable (đơn giản nhất).</p>',
                ],
                [
                    'heading' => '4. Khăn infinity loop - level 2',
                    'body'    => '<p>Khăn khép vòng, quàng 2 vòng quanh cổ. Thực chất là một miếng len hình chữ nhật rồi khâu 2 đầu với nhau. Giá trị ở việc chọn màu — dùng gradient ombre từ hồng nhạt sang hồng đậm sẽ rất ấn tượng.</p>',
                ],
                [
                    'heading' => '5. Khăn shawl tam giác - level 3',
                    'body'    => '<p>Shawl là khăn rộng hình tam giác quấn qua vai, rất nữ tính. Kỹ thuật: tăng mũi ở giữa mỗi hàng theo công thức. Dùng len mohair ánh kim sẽ ra thành phẩm như "cô tiên". Đây là dự án cuối tuần cho người đã quen.</p>',
                ],
            ],
        ],
        [
            'slug'      => 'cau-chuyen-khoi-nghiep-handmade-cua-co-chu-shop',
            'title'     => 'Khi đan len trở thành nghề: câu chuyện 5 năm của chị Hương',
            'excerpt'   => 'Từ một cuộn len giải stress sau giờ làm văn phòng đến một xưởng handmade 8 người ở Đà Lạt — đan len đã thay đổi cuộc đời chị Hương như thế nào.',
            'cover'     => '/images/1.jpg',
            'category'  => 'cau-chuyen',
            'author'    => 'Ngọc Mai',
            'date'      => '2026-03-08',
            'read_time' => 12,
            'featured'  => false,
            'tags'      => ['câu chuyện', 'khởi nghiệp', 'nghệ nhân'],
            'sections'  => [
                [
                    'heading' => 'Cuộn len đầu tiên năm 2021',
                    'body'    => '<p>"Lúc đó mình 32 tuổi, làm kế toán 10 năm, stress kinh khủng mùa cao điểm," chị Hương nhớ lại. "Thấy bạn thân đan khăn tặng con gái, mình mua thử một cuộn len cotton và kim 4mm về làm cho vui. 3 tháng sau mình đã có 20 cái khăn - đủ tặng cả họ hàng."</p>',
                ],
                [
                    'heading' => 'Từ sở thích sang đơn hàng',
                    'body'    => '<p>Đơn hàng đầu tiên đến tình cờ: một người bạn trên Facebook thấy ảnh amigurumi chị Hương đăng và đặt 5 con cho tiệc sinh nhật cháu. "Mình lấy 80k/con, làm 3 tối xong. Khách khen, đưa hình lên Insta - rồi đơn khác tới. Sau 6 tháng, đơn lẻ đã bằng lương văn phòng của mình."</p>',
                ],
                [
                    'heading' => 'Quyết định nghỉ việc',
                    'body'    => '<p>Năm 2023, chị Hương nộp đơn nghỉ và mở workshop đầu tiên ở Đà Lạt. "Ban đầu chỉ có mình với hai em học trò. Mỗi tuần dạy 2 lớp, bán sản phẩm ở chợ đêm. Rủi ro nhất là 3 tháng đầu - doanh thu bằng 60% lương cũ, tự nghi ngờ mình sai lầm."</p>',
                ],
                [
                    'heading' => 'Hiện tại: 8 nghệ nhân + xưởng 120m²',
                    'body'    => '<p>Hôm nay, xưởng của chị có 8 bạn - đều là những người phụ nữ địa phương trước đây không có việc làm ổn định. "Điều mình tự hào nhất không phải doanh thu, mà là 8 gia đình có thêm thu nhập. Đan len không chỉ là nghề - nó tạo cộng đồng."</p>',
                ],
                [
                    'heading' => 'Lời khuyên cho người bắt đầu',
                    'body'    => '<p>"Đừng nghỉ việc ngay," chị cười. "Làm sở thích trước. Nếu sau 6 tháng vẫn yêu và có đơn đều, thì mới nghĩ đến chuyện nghỉ. Và nhớ: <strong>thu nhập từ handmade không tuyến tính</strong> - có tháng 5 triệu, có tháng 50 triệu. Phải có tiết kiệm trước."</p>',
                ],
            ],
        ],
    ],
];
