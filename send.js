function SendToExec() {
    console.log("aaa");
    code = document.getElementById("code").value;
    data = {
        code: code,
        type: "php"
    }
    ajax("POST", "exec.php", data)
}

function ajax(type, url, data) {
    $.ajax({
        type: type,
        url: url,
        data: data
      }).done(function(msg) {
        alert("コードが正常に実行されました。");
      }).fail(function(msg) {
        alert("コードが正常に実行されませんでした。")
      });
}