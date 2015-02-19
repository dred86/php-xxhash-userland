<?php

namespace mheap\xxhash;

$thirtytwo1s = 2^32-1;

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

        $this->mem_total_size = 16;

        $this->seed = $seed;

        $this->v1 = $this->seed + static::PRIME1 + static::PRIME2;
        $this->v2 = $this->seed + static::PRIME2;
        $this->v3 = $this->seed + 0;
        $this->v4 = $this->seed - static::PRIME1;

        $this->total_len = 0;
        $this->memory = array();
        for ($i=0; $i < $this->mem_total_size; $i++) {
            $this->memory[$i] = null;
        }
    }

    public function getSeed() {
        return $this->seed;
    }

    public function update($bytes) {
        $bytes = array_values(unpack("C*", $bytes));

        $i = 0;
        $bytes_length = count($bytes);
        $this->memsize = count($bytes);

        $this->total_len += $bytes_length;

        $memsize = 0;

        $p = 0;

        while (($remaining = $bytes_length - $p)  > 0){
            $mem_avail = $this->mem_total_size - $memsize;

            $this->memory[$memsize] = $bytes[$p];

            if($remaining < $mem_avail) {
                $z = $memsize;
                while($z < $remaining) {
                    $currentBytes = isset($bytes[$z]) ? $bytes[$z] : null;
                    $this->memory[$z] = $currentBytes;
                    $z++;
                }
                $memsize += $remaining;
            } else {
                $this->memory[$mem_avail] = $bytes[$mem_avail];
            }

            $i = 0;
            foreach (array("v1", "v2", "v3", "v4") as $m) {
                $p32 = uint32($this->memory[$i] |
                    ($this->memory[$i+1] << 8) |
                    ($this->memory[$i+2] << 16) |
                    ($this->memory[$i+3] << 24));

                $v = uint32($this->{$m}+ $p32 * static::PRIME2);
                $v = uint32((($v << 13) | ($v >> (32 - 13))) * static::PRIME1);
                //$this->{$m} = $v;
                $i += 4;
            }

            $p += $mem_avail;
        }

        return true;
    }

    public function hash($input) {
        //$this->inputData = $input;
        $this->update($input);
        return $this->doHash();
    }

    public function doHash(){

        if ($this->total_len >= 16) {
            $h32 = (($this->v1 << 1) | ($this->v1 >> (32 - 1))) +
                (($this->v2 << 7) | ($this->v2 >> (32 - 7))) +
                (($this->v3 << 12) | ($this->v3 >> (32 - 12))) +
                (($this->v4 << 18) | ($this->v4 >> (32 - 18)));
        } else {
            $h32 = $this->seed + static::PRIME5;
        }


        $h32 = uint32($h32 + $this->total_len);

        $p = 0;
        while ($p <= ($this->memsize - 4)){
            $p32 = uint32($this->memory[$p] |
                ($this->memory[$p+1] << 8) |
                ($this->memory[$p+2] << 16) |
                ($this->memory[$p+3] << 24));
            $h32 = uint32($h32 + $p32 * static::PRIME3);
            $h32 = uint32(uint32(($h32 << 17) | ($h32 >> (32 - 17))) * static::PRIME4);
            $p += 4;
        }


        while ($p < $this->memsize) {
            $h32 = uint32($h32 + $this->memory[$p] * static::PRIME5);
            $h32 = uint32(uint32(($h32 << 11) | ($h32 >> (32 - 11))) * static::PRIME1);
            $p += 1;
        }

        $h32 ^= $h32 >> 15;
        $h32 = uint32($h32 * static::PRIME2);
        $h32 ^= $h32 >> 13;
        $h32 = uint32($h32 * static::PRIME3);
        $h32 ^= $h32 >> 16;

        return $h32;
    }
}

function uint32($x) {
    global $thirtytwo1s;
    return $x & 0b11111111111111111111111111111111;
}

