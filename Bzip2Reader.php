<?php

namespace App\Contracts;

class Bzip2Reader
{

    // ファイルから一度に読み込むバイト数(max:8192byte)
    const MAX_READ_BYTE = 8192;

    private $file_path;
    private $file;
    private $is_open = false;

    // 区切り文字に置換する対象
    private $replace_target;
    // 区切り文字
    private $delimiter;
    // 読み込み開始行数
    private $start_row;

    // 読み込み途中の文字列
    private $incomplete_string = '';
    // 今まで読んだ行数
    private $total_row_count = 0;
    // 開始行に達したかどうか
    private $is_reach_start_row = false;

    /**
     * Bzip2Reader constructor.
     * @param string $file_path 読み込むファイルパス
     * @param array $replace_target 置換対象
     * @param string $delimiter 置換後の区切り文字
     * @param int $start_row 返すファイルの開始行
     * @throws \Exception
     */
    function __construct(string $file_path, array $replace_target, string $delimiter, int $start_row = 1)
    {
        // 0行目はないので、渡されても1行目とする
        if ($start_row == 0) {
            $start_row = 1;
        }

        $this->file_path = $file_path;
        $this->replace_target = $replace_target;
        $this->delimiter = $delimiter;
        $this->start_row = $start_row;

        try {
            $this->file = bzopen($file_path, "r");
            $this->is_open = true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return array|mixed|null
     * @throws \Exception
     *
     * 呼ばれるたびに、openしたファイルのポインタを進めて文字列を読み込み、読み込みきれた分の行を配列に詰めて返す
     */
    public function getNextRows()
    {

        try {
            if (!$this->is_open) {
                return null;
            }

            // 指定された読み込み開始行に達しているか
            if ($this->is_reach_start_row) {
                // 達している場合は、次のバイト分（整形済）を返す
                return $this->readNextBytes();
            } else {
                // 達していない場合は、開始行に達するまで読み込んで返す
                return $this->shiftToStartRow();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return array
     *
     * 指定バイト数を読み込み、行ごとに分割して返す
     * 最後の要素は、中途半端な可能性があるので逃してる
     */
    private function readNextBytes()
    {

        // 決まったバイト数の文字列を読み込み
        $read_str = bzread($this->file, self::MAX_READ_BYTE);

        $read_str = str_replace($this->replace_target, $this->delimiter, $read_str);
        $result = explode($this->delimiter, $read_str);

        // 前回の読み込みで途中まで読み込んだ文字列を連結
        $result[0] = $this->incomplete_string . $result[0];

        if (!feof($this->file)) {
            // 最後の要素は行の途中の可能性があるので退避
            $this->incomplete_string = end($result);

            if (count($result) > 1) {
                // 読み込み行数が1行分に達している場合、最後の要素は退避させたので結果からは削除
                array_pop($result);
            } else {
                // resultが1行分に達していない場合、再帰的に次のbyte分を読み込む
                $result = $this->readNextBytes();
            }
        } else {
            // ファイルの終わりに達したのでファイルを閉じる
            bzclose($this->file);
            $this->is_open = false;
        }

        return $result;
    }


    /**
     * @return mixed
     *
     * 開始行を超えるまで読み込み、結果を返す
     */
    private function shiftToStartRow()
    {

        // 総読み込み行が開始行を超えるまで読み込み続ける
        while ($this->start_row > $this->total_row_count && $this->is_open) {
            // 次のバイト分（整形済）を取得
            $result = $this->readNextBytes();
            // 総読み込み行数を更新
            $this->total_row_count += count($result);
        }

        // 開始行に達しなかった場合
        if ($this->total_row_count < $this->start_row) {
            return null;
        }

        // 不要な行数分の要素を削除
        $this->is_reach_start_row = true;
        $no_need_row_count = count($result) - ($this->total_row_count - $this->start_row) - 1;
        for ($i = 0; $i < $no_need_row_count; $i++) {
            array_shift($result);
        }

        return $result;
    }
}