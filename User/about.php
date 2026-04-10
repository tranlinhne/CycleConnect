<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giới thiệu</title>

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
        }

        .image img {
            width: 100%;
            border-radius: 20px;
        }

        .highlight {
            color: #ff6600;
            font-weight: bold;
        }

        ul {
            padding-left: 20px;
        }

        ul li {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- 1. SỨ MỆNH -->
    <div class="section">
        <div class="text">
            <h2>Sứ mệnh của chúng tôi</h2>

            <p>
                Tại <span class="highlight">BikeStore</span>, chúng tôi tin rằng xe đạp không chỉ là một phương tiện di chuyển, 
                mà còn là biểu tượng của một lối sống lành mạnh, bền vững và thân thiện với môi trường.
            </p>

            <p>
                Trong bối cảnh đô thị ngày càng đông đúc và ô nhiễm, việc lựa chọn xe đạp giúp giảm thiểu khí thải, 
                tiết kiệm chi phí và mang lại nhiều lợi ích cho sức khỏe. Vì vậy, chúng tôi ra đời với mục tiêu 
                mang đến cho khách hàng những sản phẩm xe đạp chất lượng cao, đa dạng mẫu mã và phù hợp với mọi nhu cầu.
            </p>

            <p>
                Không chỉ dừng lại ở việc bán sản phẩm, chúng tôi còn mong muốn xây dựng một cộng đồng yêu xe đạp, 
                nơi mọi người có thể chia sẻ kinh nghiệm, hành trình và niềm đam mê của mình.
            </p>

            <p>
                Sứ mệnh của chúng tôi là giúp bạn <span class="highlight">di chuyển thông minh hơn – sống khỏe hơn – và bảo vệ môi trường tốt hơn</span>.
            </p>
        </div>

        <div class="image">
            <img src="assets/images/about.jpg" alt="Mission">
        </div>
    </div>

    <!-- 2. CÂU CHUYỆN -->
    <div class="section">
        <div class="text">
            <h2>Câu chuyện của chúng tôi</h2>

            <p>
                BikeStore bắt đầu từ một ý tưởng đơn giản vào năm 2024, khi những người sáng lập – 
                là các bạn trẻ yêu thích thể thao và công nghệ – nhận ra rằng việc tìm mua một chiếc xe đạp chất lượng 
                tại Việt Nam vẫn còn nhiều khó khăn.
            </p>

            <p>
                Ban đầu, chúng tôi chỉ là một cửa hàng nhỏ với số lượng sản phẩm hạn chế. Tuy nhiên, nhờ sự tin tưởng 
                và ủng hộ của khách hàng, chúng tôi đã từng bước phát triển và mở rộng thành một nền tảng bán xe đạp trực tuyến.
            </p>

            <p>
                Trong suốt quá trình phát triển, chúng tôi luôn đặt khách hàng làm trung tâm, lắng nghe phản hồi 
                và không ngừng cải tiến dịch vụ. Từ việc lựa chọn sản phẩm, tối ưu trải nghiệm website cho đến chăm sóc sau bán hàng, 
                tất cả đều nhằm mang lại sự hài lòng cao nhất.
            </p>

            <p>
                Hành trình của BikeStore vẫn đang tiếp tục, và chúng tôi hy vọng sẽ trở thành người bạn đồng hành đáng tin cậy 
                trên mọi cung đường của bạn.
            </p>
        </div>

        <div class="image">
            <img src="assets/images/about2.jpg" alt="Story">
        </div>
    </div>

    <!-- 3. GIÁ TRỊ & CAM KẾT -->
    <div class="section">
        <div class="text">
            <h2>Giá trị & cam kết</h2>

            <p>
                Chúng tôi hoạt động dựa trên những giá trị cốt lõi nhằm đảm bảo sự phát triển bền vững 
                và mang lại lợi ích lâu dài cho khách hàng:
            </p>

            <ul>
                <li><strong>Chất lượng hàng đầu:</strong> Sản phẩm được kiểm tra kỹ lưỡng trước khi đến tay khách hàng.</li>
                <li><strong>Khách hàng là trung tâm:</strong> Luôn lắng nghe và hỗ trợ khách hàng nhanh chóng.</li>
                <li><strong>Giá cả hợp lý:</strong> Cung cấp sản phẩm với mức giá cạnh tranh nhất.</li>
                <li><strong>Bền vững & xanh:</strong> Khuyến khích lối sống thân thiện với môi trường.</li>
            </ul>

            <p>
                Chúng tôi cam kết mang đến trải nghiệm mua sắm dễ dàng, an toàn và tiện lợi thông qua nền tảng website hiện đại.
            </p>

            <p>
                Khi lựa chọn BikeStore, bạn không chỉ mua một chiếc xe đạp, mà còn tham gia vào một cộng đồng 
                hướng đến tương lai xanh và khỏe mạnh hơn.
            </p>
        </div>

        <div class="image">
            <img src="assets/images/about3.jpg" alt="Values">
        </div>
    </div>

</div>

</body>
</html>

<?php include 'includes/footer.php'; ?>