<?php

namespace Tests;

use Illuminate\Support\Facades\Storage;

/**
 * Class BaseTestCase
 *
 * @package Tests
 */
class BaseTestCase extends TestCase
{
    /**
     * Get data with type
     *
     * @param array $data
     *
     * @return array
     */
    public function getDataStructure($data)
    {
        foreach ($data as $key => $val) {
            if (is_array($val) || is_object($val)) {
                $data[$key] = $this->getdataStructure($val);
            } else {
                $data[$key] = gettype($val);
            }
        }

        return $data;
    }

    public function loadDataFromJson($jsonPath, $dataNeedToReplace = [])
    {
        $json = Storage::disk('testing')->get($jsonPath);
        if (!empty($dataNeedToReplace)) {
            foreach ($dataNeedToReplace as $item) {
                $json = str_replace($item[0], $item[1], $json);
            }
        }

        return json_decode($json, true);
    }
}
