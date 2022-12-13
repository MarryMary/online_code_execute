<?php
#　メインのコード。記述されたコードをファイルに保存し、dockerで実行する
function StartAnalyze(string $code, string $lang = "php") {
    
    # 日時をファイル名にする（アクセス数が増えた場合は対策が必要かも）
    $today = "Y-m-d_H-i-s";
    # 言語名から拡張子を取得。想定外の言語の場合はfalseを返却する。
    $extension = CheckExtension($lang);
    # early return
    if(is_bool($extension)){
        return false;
    }

    # ファイル名を作成し、ファイルを作成する。
    $filename = $today.$extension;
    $result = file_put_contents($filename, $code);

    # 指定した言語のdockerイメージのタグを取得
    $docker_tag = PullDockerTag($lang);

    # ファイル作成かdockerイメージのタグ取得に問題があればアーリーリターンで終了
    if(is_bool($result) && !$result || is_bool($docker_tag) && !$docker_tag) {
        return false;
    }
    
    # dockerコマンドを実行する
    return DockerCommandExecute($filename, $lang, $docker_tag);
}

# dockerコンテナを作成して実行する
function DockerCommandExecute(string $filename, string $lang, string $docker_tag) {
    # dockerコマンドでコンテナを作り、ファイルを実行する。
    $docker_command = "docker run -i --rm --name sandbox -v code:/usr/src/code -w /usr/src/code $lang:$docker_tag $lang $filename";

    # コマンドを実行。$outputにはコマンド実行時の出力が、$resultはステータスコードが返却される
    exec($docker_command, $output, $result);
    return [$output, $result];
}

# 言語名と拡張子が書かれたJSONファイルから対応する拡張子を取得する。
function CheckExtension(string $lang = "php") {
    return ReturnJSONValue(strtolower($lang), "extension_list.json");
}

# 言語名とdockerイメージのタグが書かれたJSONファイルから対応する拡張子を取得する。
function PullDockerTag(string $lang = "php") {
    return ReturnJSONValue($lang, "extension_list.json");
}

#JSONファイルを取得して連想配列に変換
function ReturnJSONValue(string $key, string $filename) {
    # ファイルを取得して連想配列に変換
    $get_file = file_get_contents($filename);
    $json_to_assoc = json_decode($get_file, true);

    # キーがある場合は値を返却し、なければfalseを返却
    if(array_key_exists($key, $json_to_assoc)) {
        return $json_to_assoc[$key];
    } else {
        return false;
    }
}


# 実行
$result = StartAnalyze($_POST['code'], $_POST['type']);
# コマンド出力とコード実行後のステータスコード
$response = [
    "message" => $result[0],
    "res_code" => $result[1],
];

# CORS対策
header("Access-Control-Allow-Origin: *");
# $responseをJSONでレスポンスする
echo json_encode($response);