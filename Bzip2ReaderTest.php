<?php

namespace Tests\Unit;

use App\Contracts\Bzip2Reader;
use Tests\TestCase;

class Bzip2ReaderTest extends TestCase
{

    // 読み込んだbzip2ファイルの変換対象、改行コードが複数パターンあるかもなので全種類
    public $bzip2_replace_target = array("\r\n", "\r", "\n");
    // BZIP2_REPLACE_TARGETの置換後文字列、1行を判定する区切り文字
    public $bzip2_delimiter = "\n";

    // テストファイル
    public $bz2file_0rows = "0-rows.bz2";
    public $bz2file_1rows = "1-rows.bz2";
    public $bz2file_10rows = "10-rows.bz2";
    public $bz2file_300rows = "300-rows.bz2";
    public $bz2file_300rows_numbering = "300-rows-numbering.bz2";
    public $bz2file_296rows_contain_empty_rows = "296-rows-contain-empty-rows.bz2";

    /**
     * @test
     *
     * @group Bzip2Reader
     *
     * @return void
     */
    public function 指定した開始行から最終行までのデータが返ってくる()
    {

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_300rows_numbering;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [300, 0, 0, 62];

            $expect_return_row_count = $file_row_count - $start_row + 1;

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);

            $final_result = [];

            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);

                $final_result = $result;

                if ($loop_count == 1) {
                    // 一番はじめは62行目
                    $this->assertEquals($start_row, current($result));
                }
            }

            // 最後に返ってくるのは300行目
            $this->assertEquals($file_row_count, end($final_result));

            // 返ってきた行数があっているか
            $this->assertEquals($expect_return_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();
    }

    /**
     * @test
     *
     * @group Bzip2Reader
     *
     * @return void
     */
    public function _1回の読み込みでファイル全てを読み終わる()
    {

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_10rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか
            list($file_row_count, $return_row_count, $loop_count) = [10, 0, 0];

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 本当に1回の読み込みでファイル全てを読み終わったか
            $this->assertEquals($loop_count, 1);

            // ファイルの行数と返ってきた行数があっているか
            $this->assertEquals($file_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();
    }

    /**
     * @test
     *
     * @group Bzip2Reader
     *
     * @return void
     */
    public function _2回以上の読み込みでファイル全てを読み終わる()
    {

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_300rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか
            list($file_row_count, $return_row_count, $loop_count) = [300, 0, 0];

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 本当に2回以上の読み込みでファイル全てを読み終わったか
            $this->assertTrue(1 < $loop_count);

            // ファイルの行数と返ってきた行数があっているか
            $this->assertEquals($file_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();
    }

    /**
     * @test
     *
     * @group Bzip2Reader
     *
     * @return void
     */
    public function _0行のファイル読み込み()
    {

        // 正確には空の一行のみがあるファイル
        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_0rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか
            list($file_row_count, $return_row_count, $loop_count) = [1, 0, 0];

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 本当に1回の読み込みでファイル全てを読み終わったか
            $this->assertEquals($loop_count, 1);

            // ファイルの行数と返ってきた行数があっているか
            $this->assertEquals($file_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();
    }

    /**
     * @test
     *
     * @group Bzip2Reader
     *
     * @return void
     */
    public function _1行のファイル読み込み()
    {

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_1rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか
            list($file_row_count, $return_row_count, $loop_count) = [1, 0, 0];

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 本当に1回の読み込みでファイル全てを読み終わったか
            $this->assertEquals($loop_count, 1);

            // ファイルの行数と返ってきた行数があっているか
            $this->assertEquals($file_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();
    }

    /**
     * @test
     *
     * @group Bzip2Reader
     *
     * @return void
     */
    public function 空行を含むファイルの読み込み()
    {

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_296rows_contain_empty_rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか
            list($file_row_count, $return_row_count, $loop_count) = [296, 0, 0];

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // ファイルの行数と返ってきた行数があっているか
            $this->assertEquals($file_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();
    }

    /**
     * @test
     *
     * @group Bzip2Reader
     *
     * @return void
     */
    public function ファイルの読み込み開始行が0()
    {

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_10rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [10, 0, 0, 0];

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // ファイルの行数と返ってきた行数があっているか
            $this->assertEquals($file_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_300rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [300, 0, 0, 0];

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // ファイルの行数と返ってきた行数があっているか
            $this->assertEquals($file_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();
    }

    /**
     * @test
     *
     * @group Bzip2Reader
     *
     * @return void
     */
    public function ファイルの読み込み開始行が1()
    {

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_10rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [10, 0, 0, 0];

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // ファイルの行数と返ってきた行数があっているか
            $this->assertEquals($file_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_300rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [300, 0, 0, 0];

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // ファイルの行数と返ってきた行数があっているか
            $this->assertEquals($file_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();
    }

    /**
     * @test
     *
     * @group Bzip2Reader
     *
     * @return void
     */
    public function ファイルの読み込み開始行がファイルの最終行より前()
    {

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_10rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [10, 0, 0, 5];

            $expect_return_row_count = $file_row_count - $start_row + 1;

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 返ってきた行数があっているか
            $this->assertEquals($expect_return_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_300rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [300, 0, 0, 5];

            $expect_return_row_count = $file_row_count - $start_row + 1;

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 返ってきた行数があっているか
            $this->assertEquals($expect_return_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();
    }

    /**
     * @test
     *
     * @group Bzip2Reader
     *
     * @return void
     */
    public function ファイルの読み込み開始行がファイルの最終行()
    {

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_10rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [10, 0, 0, 10];

            $expect_return_row_count = $file_row_count - $start_row + 1;

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 返ってきた行数があっているか
            $this->assertEquals($expect_return_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_300rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [300, 0, 0, 300];

            $expect_return_row_count = $file_row_count - $start_row + 1;

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 返ってきた行数があっているか
            $this->assertEquals($expect_return_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();
    }

    /**
     * @test
     *
     * @group Bzip2Reader
     *
     * @return void
     */
    public function ファイルの読み込み開始行がファイルの最終行より後()
    {

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_10rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [10, 0, 0, 15];

            $expect_return_row_count = 0;

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 返ってきた行数があっているか
            $this->assertEquals($expect_return_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_300rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [300, 0, 0, 305];

            $expect_return_row_count = 0;

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 返ってきた行数があっているか
            $this->assertEquals($expect_return_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();
    }

    /**
     * @test
     *
     * bzreadの指定byte数が8192、ファイルはsample.log_300-rows.bz2の前提であれば、1回目と2回目は62行を読み込むのでその前提でのテスト項目
     * @group Bzip2Reader
     *
     * @return void
     */
    public function 読み込み開始行数への達し方()
    {

        // 1回の読み込みで開始行数に達する（ちょうど）
        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_300rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [300, 0, 0, 62];

            $expect_return_row_count = $file_row_count - $start_row + 1;

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 返ってきた行数があっているか
            $this->assertEquals($expect_return_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();

        // 1回の読み込みで開始行数に達する（1行多く）
        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_300rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [300, 0, 0, 61];

            $expect_return_row_count = $file_row_count - $start_row + 1;

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 返ってきた行数があっているか
            $this->assertEquals($expect_return_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();

        // 1回の読み込みで開始行数に達する（2行以上多く）
        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_300rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [300, 0, 0, 50];

            $expect_return_row_count = $file_row_count - $start_row + 1;

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 返ってきた行数があっているか
            $this->assertEquals($expect_return_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();

        // 1回の読み込みで開始行数に達する（ちょうど）
        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_300rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [300, 0, 0, 124];

            $expect_return_row_count = $file_row_count - $start_row + 1;

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 返ってきた行数があっているか
            $this->assertEquals($expect_return_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();

        // 一回の読み込みで開始行数に達する（1行多く）
        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_300rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [300, 0, 0, 123];

            $expect_return_row_count = $file_row_count - $start_row + 1;

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 返ってきた行数があっているか
            $this->assertEquals($expect_return_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();

        // 一回の読み込みで開始行数に達する（2行以上多く）
        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_" . $this->bz2file_300rows;

            // ファイルの行数、返ってきた行数の総和、getNexRows()から何回データが返ってきたか、開始行
            list($file_row_count, $return_row_count, $loop_count, $start_row) = [300, 0, 0, 120];

            $expect_return_row_count = $file_row_count - $start_row + 1;

            $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter, $start_row);
            while ($result = $reader->getNextRows()) {
                $loop_count++;
                $return_row_count += count($result);
            }

            // 返ってきた行数があっているか
            $this->assertEquals($expect_return_row_count, $return_row_count);

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();
    }

    /**
     * @test
     *
     * @group Bzip2Reader
     *
     * @return void
     */
    public function 存在しないファイル名()
    {

        \Closure::bind(function () {

            $file_path = env('TEST_FILE_PATH') . "_not_exist_file_name.bz2";

            try {
                $reader = new Bzip2Reader($file_path, $this->bzip2_replace_target, $this->bzip2_delimiter);
                // 例外が発生するはずのテストで例外が発生しなかったのでfail
                $this->fail('例外発生なし');
            } catch (\Exception $e) {
                // エラーコードでの比較
                $this->assertEquals(0, $e->getCode());
            }

        }, $this, 'App\Contracts\Bzip2Reader')->__invoke();
    }

}
