<?php

namespace Tests\Unit\Services\Transformers;

use PHPUnit\Framework\TestCase;
use App\Services\Transformers\TransformerFactory;
use App\Services\Transformers\Csv\CsvTransformer;
use App\Services\Transformers\Json\JsonTransformer;
use App\Services\Transformers\Xml\XmlTransformer;
use App\Services\Transformers\Xlsx\XlsxTransformer;
use InvalidArgumentException;

class TransformerFactoryTest extends TestCase
{
    public function testCreateJsonTransformer()
    {
        $transformer = TransformerFactory::createTransformer('json');
        $this->assertInstanceOf(JsonTransformer::class, $transformer);
    }
    
    public function testCreateXmlTransformer()
    {
        $transformer = TransformerFactory::createTransformer('xml');
        $this->assertInstanceOf(XmlTransformer::class, $transformer);
    }
    
    public function testCreateCsvTransformer()
    {
        $transformer = TransformerFactory::createTransformer('csv');
        $this->assertInstanceOf(CsvTransformer::class, $transformer);
    }
    
    public function testCreateXlsxTransformer()
    {
        $transformer = TransformerFactory::createTransformer('xlsx');
        $this->assertInstanceOf(XlsxTransformer::class, $transformer);
    }
    
    public function testInvalidTransformerType()
    {
        $this->expectException(InvalidArgumentException::class);
        TransformerFactory::createTransformer('invalid');
    }
} 