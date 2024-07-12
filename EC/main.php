<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ショッピングサイトへようこそ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="./style.css">
</head>
<body>
    <header>
        <nav>
            <div class="nav-wrapper">
                <a class="brand-logo black-text nav-font">Lélian Luxe</a>
                <ul id="nav-mobile" class="right hide-on-med-and-down">
                    <li><a href="./cart/cart.html" class="waves-effect waves-light btn">商品カート</a></li>
                    <li><a href="./login/login.html" class="waves-effect waves-light btn">ログイン</a></li>
                    <li><a href="./login/register.html" class="waves-effect waves-light btn">新規登録</a></li>
                </ul>
            </div>
            <div class="nav-content">
                <ul class="tabs tabs-transparent">
                    <li class="tab"><a href="#all">すべての商品</a></li>
                    <li class="tab"><a href="#new">新着商品</a></li>
                    <li class="tab"><a href="#popular">人気商品</a></li>
                    <li class="tab"><a href="#sale">セール商品</a></li>
                </ul>
            </div>
        </nav>
    </header>
    
    <main>
        <div class="container padding">
            <!-- Search Form -->
            <form action="search.php" method="GET">
                <div class="input-field search">
                    <input id="search" type="text" name="query" required>
                    <label for="search">商品を検索</label>
                <button type="submit" class="waves-effect waves-light btn">検索</button>
                </div>
            </form>

            <h2 class="center-align">おすすめ商品</h2>
            <!-- Recommendation Carousel -->
            <div class="carousel carousel-slider center">
                <div class="carousel-item" href="./image/shoulderbag.png">
                    <img src="./image1.jpg" alt="商品1">
                    <h2 class="white-text">商品1</h2>
                    <p class="white-text">おすすめ商品1</p>
                </div>
                <div class="carousel-item" href="./image2.jpg">
                    <img src="./image2.jpg" alt="商品2">
                    <h2 class="white-text">商品2</h2>
                    <p class="white-text">おすすめ商品2</p>
                </div>
                <div class="carousel-item" href="./image3.jpg">
                    <img src="./image3.jpg" alt="商品3">
                    <h2 class="white-text">商品3</h2>
                    <p class="white-text">おすすめ商品3</p>
                </div>
                <div class="carousel-item" href="./image4.jpg">
                    <img src="./image4.jpg" alt="商品4">
                    <h2 class="white-text">商品4</h2>
                    <p class="white-text">おすすめ商品4</p>
                </div>
            </div>

            <div class="row">
                <!-- Card 1 -->
                <div class="col s12 m6 l3">
                    <div class="card hoverable">
                        <div class="card-image">
                            <img src="./image/shoulderbag.png" alt="Product 1">
                        </div>
                        <div class="card-content">
                            <span class="card-title">ショルダーバッグ</span>
                        </div>
                        <div class="card-action">
                            <a href="recommend1.html">ショルダーバッグのおすすめ商品を見る</a>
                        </div>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="col s12 m6 l3">
                    <div class="card hoverable">
                        <div class="card-image">
                            <img src="./image/clutchbag.png" alt="Product 2">
                        </div>
                        <div class="card-content">
                            <span class="card-title">クラッチバッグ</span>
                        </div>
                        <div class="card-action">
                            <a href="recommend2.html">クラッチバッグのおすすめ商品を見る</a>
                        </div>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="col s12 m6 l3">
                    <div class="card hoverable">
                        <div class="card-image">
                            <img src="./image/briefcase.png" alt="Product 3">
                        </div>
                        <div class="card-content">
                            <span class="card-title">ビジネスバッグ</span>
                        </div>
                        <div class="card-action">
                            <a href="recommend3.html">ビジネスバッグのおすすめ商品を見る</a>
                        </div>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="col s12 m6 l3">
                    <div class="card hoverable">
                        <div class="card-image">
                            <img src="./image2.jpg" alt="Product 4">
                        </div>
                        <div class="card-content">
                            <span class="card-title">商品4</span>
                            <p>この商品は高評価を得ています。</p>
                        </div>
                        <div class="card-action">
                            <a href="#">詳細を見る</a>
                        </div>
                    </div>
                </div>
                <!-- 追加のカードはここに続けて追加できます -->
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <div class="row">
                <div class="col l6 s12">
                    <h5 class="white-text">Lélian Luxe</h5>
                    <p class="grey-text text-lighten-4">洗練されたあなたのための特別な一品を</p>
                </div>
                <div class="col l4 offset-l2 s12">
                    <ul>
                        <li><a class="grey-text text-lighten-3" href="./cart/cart.html">商品カート</a></li>
                        <li><a class="grey-text text-lighten-3" href="./login/login.html">ログイン</a></li>
                        <li><a class="grey-text text-lighten-3" href="./login/register.html">新規登録</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            <div class="container">
                &copy; 2024 Shopping Site. All rights reserved.
            </div>
        </div>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="scripts.js"></script>
</body>
</html>
