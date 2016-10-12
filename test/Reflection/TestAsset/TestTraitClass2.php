<?php
//issue #6620
trait TestTrait
{
	/**
	* @var bool
	*/
	protected $dummy = false;

	/**
	* @return bool
	*/
	public function getDummy()
	{
		return $this->dummy;
	}

	/**
	* @param bool $dummy
	* @return TestTrait Provides a fluent interface
	*/
	public function setDummy($dummy)
	{
		$this->dummy = boolval($dummy);
		return $this;
	}
}
