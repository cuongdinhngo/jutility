<?php

namespace Cuongnd88\Jutility\Support;

use Illuminate\Support\Facades\Validator;

class CSV
{
    /**
     * CSV data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Error data
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Validator configuration
     *
     * @var array
     */
    protected $validatorConfig = [];

    /**
     * Get file real path
     *
     * @param mixed $file
     *
     * @return mixed
     */
    public function getFileRealPath($file)
    {
        if (is_string($file)) {
            return $file;
        }
        if (is_array($file) && isset($file['tmp_name'])) {
            return $file["tmp_name"];
        }
        if (is_object($file)) {
            return $file->getRealPath();
        }
    }

    /**
     * Read CSV file
     *
     * @param mixed $file            CSV file path
     * @param array $standardHeader  CSV header standard
     * @param mixed $validatorConfig Validation config
     *
     * @return mixed
     */
    public function read($file, array $standardHeader = [], $validatorConfig = null)
    {
        return $this->parseCsv($file, $standardHeader, $validatorConfig);
    }

    /**
     * Parse CSV file
     *
     * @param mixed $file            CSV file path
     * @param array $standardHeader  CSV header standard
     * @param mixed $validatorConfig Validation config
     *
     * @return mixed
     */
    public function parseCsv($file, array $standardHeader = [], $validatorConfig = null)
    {
        $content = fopen($this->getFileRealPath($file), "r");

        if (false === empty($standardHeader)) {
            $fileHeader = fgetcsv($content);
            $fileHeader = array_map('trim', $fileHeader);

            if (array_values($standardHeader) != $fileHeader) {
                throw new \Exception('Invalid Header');
            }
        }

        if ($validatorConfig) {
            $this->validatorConfig['rules'] = $validatorConfig['rules'] ?? [];
            $this->validatorConfig['messages'] = $validatorConfig['messages'] ?? [];
            $this->validatorConfig['attributes'] = isset($validatorConfig['attributes']) && $validatorConfig['attributes'] ? $validatorConfig['attributes'] : $standardHeader;
        }

        $number = 1;
        
        while ($line = fgetcsv($content)) {

            if (is_null($validatorConfig)) {
                $this->data[] = $line;
                continue;
            }

            $line = array_combine(array_keys($standardHeader), $line);

            $validator = Validator::make(
                            $line,
                            $this->validatorConfig['rules'],
                            $this->validatorConfig['messages'],
                            $this->validatorConfig['attributes']
                        );

            if ($validator->fails()) {
                $this->errors[$number] = $validator->errors()->toArray();
            }

            $this->data[$number] = $line;
            $number++;
        }

        return $this;
    }

    /**
     * Get CSV data
     *
     * @return array
     */
    public function get()
    {
        return $this->data;
    }

    /**
     * Get validator errors
     *
     * @return array
     */
    public function validatorErrors()
    {
        return $this->errors;
    }

    /**
     * Filter CSV data
     *
     * @return array
     */
    public function filter()
    {
        if ($this->validatorConfig) {
            $errors = array_keys($this->errors);
            $errorData = array_filter($this->data, function ($key) use ($errors) {
                return in_array($key, $errors);
            }, ARRAY_FILTER_USE_KEY);
            return ['validated' => array_diff_key($this->data, $errorData), 'error' => $errorData];
        } else {
            return ['validated' => $this->data, 'error' => null];
        }
    }

    /**
     * Save CSV
     * @param  string $fileName
     * @param  array  $data
     * @param  mixed  $header
     *
     * @return void
     */
    public function save(string $fileName, array $data, $header = null)
    {
        try {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $fileName . '.csv');
            $output = fopen('php://output', 'w');

            if (config('csv.utf-8-bom')) {
                fputs($output, "\xEF\xBB\xBF"); // use UTF-8 BOM
            }

            $csvHeader = is_null($header) && is_array($data[0]) ? array_keys($data[0]) : $header;
            fputcsv($output, $csvHeader);

            foreach ($data as $items) {
                fputcsv($output, $items);
            }
            fclose($output);
            exit;
        } catch (\Exception $ex) {
            report($ex);
            throw new \Exception("Save Fail");
        }
    }
}
