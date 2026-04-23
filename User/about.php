<?php include 'includes/header.php'; ?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        background: #f5f7fa;
        color: #333;
    }

    .container {
        width: 90%;
        max-width: 1200px;
        margin: auto;
        padding: 60px 0;
    }

    .section {
        display: flex;
        align-items: center;
        gap: 50px;
        margin-bottom: 100px;

        opacity: 0;
        transform: translateY(30px);
        transition: 0.6s;
    }

    .section.show {
        opacity: 1;
        transform: translateY(0);
    }

    .section:nth-child(even) {
        flex-direction: row-reverse;
    }

    .text {
        flex: 1;
    }

    .text h2 {
        font-size: 34px;
        margin-bottom: 20px;
        color: #222;
    }

    .text p {
        line-height: 1.8;
        margin-bottom: 15px;
        color: #555;
    }

    .image {
        flex: 1;
        overflow: hidden;
        position: relative;
    }

    .image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: 0.5s;
    }

    /* SHAPE */
    .section-1 .image {
        clip-path: polygon(
            0 8%, 10% 4%, 25% 2%, 45% 5%, 60% 3%, 75% 6%, 90% 4%, 100% 8%,
            100% 85%,
            90% 82%, 75% 78%, 60% 75%, 50% 73%, 40% 75%, 25% 78%, 10% 82%,
            0 85%
        );
    }

    .section-2 .image {
        clip-path: polygon(0 0, 100% 8%, 100% 100%, 0 92%);
    }

    .section-3 .image {
        border-radius: 40% 60% 60% 40% / 60% 30% 70% 40%;
    }

    /* IMAGE EFFECT */
    .section-1 .image img {
        transform: scale(1.4) translateY(-50px);
    }

    .section-1 .image:hover img {
        transform: scale(1.5) translateY(-50px);
    }

    .section-2 .image:hover img,
    .section-3 .image:hover img {
        transform: scale(1.1);
    }

    .highlight {
        color: #28a745;
        font-weight: bold;
    }

    ul {
        padding-left: 20px;
    }

    ul li {
        margin-bottom: 10px;
    }

    @media (max-width: 768px) {
        .section {
            flex-direction: column !important;
            text-align: center;
        }
    }
</style>

<div class="container">

    <!-- 1 -->
    <div class="section section-1">
        <div class="text">
            <h2>Sứ mệnh của GreenRide</h2>

            <p>
                Tại <span class="highlight">GreenRide</span>, chúng tôi tin rằng xe đạp không chỉ là phương tiện di chuyển 
                mà còn là <span class="highlight">giải pháp bền vững cho sức khỏe và môi trường</span>.
            </p>

            <p>
                Trong bối cảnh thị trường mua bán xe đạp còn <span class="highlight">phân mảnh và thiếu minh bạch</span>, 
                GreenRide được xây dựng như một nền tảng kết nối người mua và người bán một cách 
                <span class="highlight">an toàn, hiệu quả</span>.
            </p>

            <p>
                Chúng tôi hướng đến việc 
                <span class="highlight">mua bán dễ dàng hơn – giao dịch an toàn hơn – và sống xanh hơn mỗi ngày</span>.
            </p>
        </div>

        <div class="image">
            <img src="assets/images/about.jpg" alt="Mission">
        </div>
    </div>

    <!-- 2 -->
    <div class="section section-2">
        <div class="text">
            <h2>Câu chuyện của chúng tôi</h2>

            <p>
                Thị trường xe đạp hiện nay chủ yếu diễn ra trên <span class="highlight">mạng xã hội và nền tảng rao vặt</span>, 
                thiếu công cụ <span class="highlight">chuyên biệt để tìm kiếm và đánh giá</span>.
            </p>

            <p>
                GreenRide ra đời nhằm giải quyết vấn đề đó, cho phép người dùng tìm xe theo 
                <span class="highlight">tiêu chí rõ ràng</span> và giúp người bán 
                <span class="highlight">tiếp cận đúng khách hàng</span>.
            </p>

            <p>
                Nền tảng tích hợp các tính năng như 
                <span class="highlight">đăng tin, tìm kiếm thông minh, chat trực tiếp và đánh giá uy tín</span>.
            </p>
        </div>

        <div class="image">
            <img src="assets/images/bikes-online.png" alt="Story">
        </div>
    </div>

    <!-- 3 -->
    <div class="section section-3">
        <div class="text">
            <h2>Giá trị & cam kết</h2>

            <p>
                GreenRide xây dựng môi trường giao dịch 
                <span class="highlight">minh bạch và đáng tin cậy</span> dựa trên các giá trị:
            </p>

            <ul>
                <li>Minh bạch: <span class="highlight">Thông tin rõ ràng, dễ kiểm chứng</span>.</li>
                <li>An toàn: <span class="highlight">Kết nối trực tiếp, hạn chế rủi ro</span>.</li>
                <li>Kiểm định: <span class="highlight">Đánh giá chất lượng xe</span>.</li>
                <li>Uy tín: <span class="highlight">Đánh giá sau giao dịch</span>.</li>
                <li>Hỗ trợ: <span class="highlight">Xử lý tranh chấp nhanh chóng</span>.</li>
            </ul>

            <p>
                Trong tương lai, GreenRide sẽ mở rộng 
                <span class="highlight">thanh toán online, vận chuyển và chatbot hỗ trợ</span>.
            </p>
        </div>

        <div class="image">
            <img src="assets/images/bike-repair.webp" alt="Values">
        </div>
    </div>

</div>

<script>
    const sections = document.querySelectorAll('.section');

    function showSections() {
        const triggerBottom = window.innerHeight * 0.85;

        sections.forEach(section => {
            const boxTop = section.getBoundingClientRect().top;

            if (boxTop < triggerBottom) {
                section.classList.add('show');
            }
        });
    }

    window.addEventListener('scroll', showSections);
    window.addEventListener('load', showSections);
</script>

<?php include 'includes/footer.php'; ?>