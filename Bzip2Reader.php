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
    // 今まで返した行数
    private $total_row_count = 0;
    // 開始行へのshiftをしたかどうか
    private $doStartShift = false;

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
            if ($this->is_open) {
                // ファイルがopenしている場合、次のbyte分読み込み、行ごとの配列を返す
                $result = $this->readNextBytes();
                // 総読み込み行数を更新
                $this->total_row_count += count($result);

                if ($this->doStartShift) {
                    // すでにshiftしてあれば、必ず開始行に達しているのでそのまま返す
                    return $result;
                } elseif ($this->start_row <= $this->total_row_count) {
                    // 総読み込み行数が開始行を一回も超えてない かつ 開始行に達している場合、不要行を削除して返す
                    return $this->shiftToStartRow($result);
                } else {
                    // 開始行に達していない場合、値は返さずに読み込みを継続する（ファイルの終わりに達していなければ）
                    if (!$this->is_open) {
                        return null;
                    }

                    return $this->getNextRows();
                }
            } else {
                // ファイルを最後まで読み込んだので終了
                return null;
            }
        } catch (\Exception $e) {

            throw $e;
        }
    }

    /**
     * @return array
     */
    private function readNextBytes()
    {

        // 決まったバイト数の文字列を読み込み
        $read_str = bzread($this->file, self::MAX_READ_BYTE);

        // 複数パターンの区切り文字を一つに置換してまとめる
        $read_str = str_replace($this->replace_target, $this->delimiter, $read_str);

        // 一行ごとに分割
        $result = explode($this->delimiter, $read_str);

        // 前回の読み込みで途中まで読み込んだ文字列を連結
        $result[0] = $this->incomplete_string . $result[0];

        // 今回で読み込み未完了の場合
        if (!feof($this->file)) {
            // 最後の要素は行の途中の可能性があるので退避
            $this->incomplete_string = end($result);

            // 読み込み行数が1行分に達している場合
            if (1 < count($result)) {
                // 最後の要素は退避させたので削除
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
     * @param $shift_result
     * @return mixed
     */
    private function shiftToStartRow($shift_result)
    {

        // 次回以降はshift不要なのでshift済みフラグ立てる
        $this->doStartShift = true;

        // 不要な行数分の要素を削除（$start_row行も必要なので-1する）
        $no_need_row_count = count($shift_result) - ($this->total_row_count - $this->start_row) - 1;

        for ($i = 0; $i < $no_need_row_count; $i++) {
            array_shift($shift_result);
        }

        return $shift_result;
    }
}