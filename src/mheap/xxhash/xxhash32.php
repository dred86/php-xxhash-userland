<?php

namespace mheap\xxhash;

class xxhash32
{

    const PRIME1 = 2654435761;
    const PRIME2 = 2246822519;
    const PRIME3 = 3266489917;
    const PRIME4 = 668265263;
    const PRIME5 = 374761393;


    protected $seed = 0;
    protected $inputData = "";

    public function __construct($seed) {
        $this->seed = $seed;

        $this->v1 = $this->seed + static::PRIME1 + static::PRIME2;
        $this->v2 = $this->seed + static::PRIME2;
        $this->v3 = $this->seed + 0;
        $this->v4 = $this->seed - static::PRIME1;

        $this->total_length = 0;

        $this->memsize = 0;
    }

    public function getSeed() {
        return $this->seed;
    }

    public function update($string) {
        $data = array_values(unpack("C*", $string));

        $p = 0;
        $len = count($data);
        $bEnd = $p + $len;

        if ($len == 0){ return; }

        $this->total_length += $len;

        if ($this->memsize == 0) {
            $this->memory = array();
        }

        while ($d = array_shift($data)) {
            if ($this->memsize < 16) {
                $this->memory[] = $d;
                $this->memsize++;
            // We have more data than will fit in memory.
            // WHAT DO WE DO NOW!?
            } else {
                print_r($data);die;
            }
        }

        return $this;

    }

    public function digest() {

        $p = 0;
        $bEnd = $this->memsize;
        $u = 0;

        $h32 = $this->seed + static::PRIME5;
        echo $h32;die;

        $h32 += $this->memsize;

        $p = 0;
        while ($p <= ($bEnd - 4)) {
            $p32 = (
                ($this->memory[$p]) |
                ($this->memory[$p+1] << 8) |
                ($this->memory[$p+2] << 16) |
                ($this->memory[$p+3] << 24)
            );

            $h32 = uint32($h32 + ($p32 * static::PRIME3));
            $h32 = uint32(rotl($h32, 17) * static::PRIME4);
            $p += 4;
        }

        while ($p < $bEnd) {
            $h32 = uint32($h32 + $this->memory[$p] * static::PRIME5);
            $h32 = uint32(rotl($h32, 17) * static::PRIME4);
            $p++;
        }

        $h32 ^= $h32 >> 15;
        $h32 = uint32($h32 * static::PRIME2);
        $h32 ^= $h32 >> 13;
        $h32 = uint32($h32 * static::PRIME3);
        $h32 ^= $h32 >> 16;

        return dechex($h32);

    }

}

function uint32($x) {
    return $x & 0b11111111111111111111111111111111;
}

function rotl($value,$amount) {
    if ($amount>0) {
        $amount %= 32;
        $value = ($value<<$amount) | ($value>>(32-$amount));
    }
    return $value;
}

function rotr($value,$amount) {
    if ($amount>0) {
        $amount %= 32;
        $value = ($value>>$amount) | ($value<<(32-$amount));
    }
    return $value;
}
