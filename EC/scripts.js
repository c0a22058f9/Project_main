document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.carousel');
    var instances = M.Carousel.init(elems, {
        fullWidth: true,
        indicators: true
    });

    // 自動スクロール機能
    setInterval(function() {
        var instance = M.Carousel.getInstance(elems[0]); // 最初のカーセルインスタンスを取得
        instance.next(); // 次のスライドへ移動
    }, 5000); // 5秒ごとに実行
});
