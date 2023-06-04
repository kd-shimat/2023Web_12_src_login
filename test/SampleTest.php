
<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\WebDriverBy;

class SampleTest extends TestCase
{
    protected $pdo; // PDOオブジェクト用のプロパティ(メンバ変数)の宣言
    protected $driver;

    public function setUp(): void
    {
        // PDOオブジェクトを生成し、データベースに接続
        $dsn = "mysql:host=db;dbname=login;charset=utf8";
        $user = "denshi";
        $password = "kobe";
        try {
            $this->pdo = new PDO($dsn, $user, $password);
        } catch (Exception $e) {
            echo 'Error:' . $e->getMessage();
            die();
        }

        #XAMPP環境で実施している場合、$dsn設定を変更する必要がある
        //ファイルパス
        $rdfile = __DIR__ . '/../src/classes/dbdata.php';
        $val = "host=db;";

        //ファイルの内容を全て文字列に読み込む
        $str = file_get_contents($rdfile);
        //検索文字列に一致したすべての文字列を置換する
        $str = str_replace("host=localhost;", $val, $str);
        //文字列をファイルに書き込む
        file_put_contents($rdfile, $str);

        // chrome ドライバーの起動
        $host = 'http://172.17.0.1:4444/wd/hub'; #Github Actions上で実行可能なHost
        // chrome ドライバーの起動
        $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
    }

    public function testRegister()
    {
        // 指定URLへ遷移 (Google)
        $this->driver->get('http://php/src/register.html');

        // inputタグの要素を取得
        $element_input = $this->driver->findElements(WebDriverBy::tagName('input'));
        // 要素にそれぞれ値を入力
        $element_input[0]->sendKeys('kobe');
        $element_input[1]->sendKeys('denshi');
        $element_input[2]->sendKeys('神戸電子');

        // 画面遷移実行
        $element_input[3]->submit();

        //データベースの値を取得
        $sql = 'select * from users where userId = ? and password = ?';       // SQL文の定義
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['kobe', 'denshi']);
        $count = $stmt->rowCount();                          // レコード数の取得
        // assert
        $this->assertEquals('1', $count, 'ユーザー登録処理に誤りがあります。');
    }

    public function testLogin()
    {
        // 指定URLへ遷移 (Google)
        $this->driver->get('http://php/src/login.html');

        // inputタグの要素を取得
        $element_input = $this->driver->findElements(WebDriverBy::tagName('input'));
        // 要素にそれぞれ値を入力
        $element_input[0]->sendKeys('kobe');
        $element_input[1]->sendKeys('denshi');

        // 画面遷移実行
        $element_input[2]->submit();

        // divタグの要素を取得
        $element_div = $this->driver->findElement(WebDriverBy::tagName('div'));

        // assert
        $this->assertStringContainsString('神戸電子', $element_div->getText(), '認証処理に誤りがあります。');
    }
}
