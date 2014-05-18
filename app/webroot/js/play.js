var response_json;
var index = 1;

$(function(){
  init();
  $(document).on("click", ".btn_", function(){
    // 2問目以降
    var choice_id = $(this).data("value");
    var reply = $(this).data("reply");
    index++;

    $.ajax({
      type: "GET",
      url: "/cakephp/api/answer",
      data: {"karte_id":response_json.karte_id, "choice_id":choice_id, "index": index}
    }).done(function(data) {
      // 返答を返して会話してる感を出す
      $("#textarea").html(reply);
      $("#btn_div").hide();
      // 描画
      window.setTimeout(function(){
        draw(data);
        $("#btn_div").show();
        response_json = data;
      }, 2500);
    });
  });
});

/**
 * 初期化処理いろいろ
 */
function init(){
  $.ajax({
    type: "GET",
    url: "/cakephp/api/start",
    data: {"user":"hoge"}
  }).done(function(data){
    // 描画
    draw(data);
    response_json = data;

    // モーダルにグラフを仕込む
    var ctx = document.getElementById("myChart").getContext("2d");
    var myNewChart = new Chart(ctx).PolarArea(data);
  });
}

/**
 * データをテンプレートに流し込む
 *
 */
function draw(data){
  if(data.info.state == "question"){
    $("#joi_img").attr("src",data.image);
    // 質問文
    (function(){
      $("#textarea").html("");
      var i = 0;
      var text =  data.question.text;
      var render = function() {
        $("#textarea").append(text.charAt(i));
        var timer = setTimeout(render, 150);
        i++;
        if (text.length == i)
          clearTimeout(timer);
      };
      render();
    })();
    response_json = data;
    
    // 選択肢
    $.each(data.question.choices, function(i){
      $("#btn_"+i).attr("data-value",data.question.choices[i].id)
                  .attr("data-reply",data.question.choices[i].reply);
      $("#btn_caption_"+i).html(data.question.choices[i].text)
                          .attr("data-value",data.question.choices[i].id)
                          .attr("data-reply",data.question.choices[i].reply);
    });
  }else{
    // 結果発表
    $("#textarea").html(data.result.text)
                  .html(data.result.html);
    // 女医
    $("#joi_img").remove();
    $("#btn_div").remove();
    $("#_____01").css("backgroundImage", "url(" + data.image + "), url(/cakephp/img/app/bg03.png)");

    console.log(data);
  }
}
