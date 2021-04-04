<?php

use Pho\Database\Model;
use PHPUnit\Framework\TestCase;

class DumbModel extends Model
{
    protected $dates = [
        'deleted_at',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    protected $dateFormat = 'U';

    public function getKey1Attribute($value)
    {
        return strtoupper($value);
    }
}

class ModelTest extends TestCase
{
    public function testGetAttribute()
    {
        $model = new DumbModel();
        $model->key1 = 'hello';
        $model->key2 = 123;
        $model->is_admin = 1;
        $model->deleted_at = $now = new DateTime();

        // getAttributeValue method
        $this->assertEquals('HELLO', $model->getAttributeValue('key1'));
        $this->assertEquals(123, $model->getAttributeValue('key2'));
        $this->assertEquals(true, $model->getAttributeValue('is_admin'));
        $this->assertEquals($now->format('U'), $model->getAttributeValue('deleted_at')->format('U'));

        // getAttribute method
        $this->assertEquals(null, $model->getAttribute(null));
        $this->assertEquals(null, $model->getAttribute('getAttribute'));
        $this->assertEquals('HELLO', $model->getAttribute('key1'));
        $this->assertEquals(123, $model->getAttribute('key2'));
        $this->assertEquals(true, $model->getAttribute('is_admin'));
        $this->assertEquals($now->format('U'), $model->getAttribute('deleted_at')->format('U'));
    }
}
