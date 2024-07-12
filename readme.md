```
/ (root)
│
├── index.html (トップページ)
├── login/
│   ├── login.html (ログインページ)
│   └── register.html (新規登録ページ)
├── products/
│   ├── index.html (全商品一覧ページ)
│   ├── product1.html (商品1の詳細ページ)
│   ├── product2.html (商品2の詳細ページ)
│   └── ... (その他の商品ページ)
├── images/
│   ├── ... (商品の画像ファイル)
│   └── ... (その他の画像ファイル)
└── ... (その他のページ)

データベース名はecdatabese

CREATE TABLE users (
    user_id INTEGER AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    birthday DATE NOT NULL,
    address VARCHAR(255),
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);
CREATE TABLE user_verification (
    verification_id INTEGER AUTO_INCREMENT PRIMARY KEY,
    user_id INTEGER NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

商品ページはmerchandise.html?id=x(x)はテーブル内のアドレスで表示できる

商品用データベースはproducts 画像は3つまでで 自動で作成されるので入力の必要はなし
USE product_db;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT NOT NULL,
    stock INT NOT NULL,
    image1 VARCHAR(255),
    image2 VARCHAR(255),
    image3 VARCHAR(255)
);
INSERT INTO products (name, price, description, stock, image1, image2, image3)
VALUES ('商品名', 1990, '商品説明', 10, 'merchandise1.jpg', 'merchandise2.jpg', 'merchandise3.png')

--------------------
TODO
--------------------
□検索バー
☑ページとデータベース
□レビュー
□カード情報
□ポイント
--------------------
```
