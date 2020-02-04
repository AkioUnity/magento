<?php

namespace Iconneqt\Api\Rest\Resources;

/*
The MIT License (MIT)

Copyright (c) 2018, Advanced CRMMail Technology B.V., Netherlands

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
class ListField extends AbstractField
{

	private $date;
	private $userid;
	private $default;
	private $required;
	private $settings;
	private $role;

	public function __construct(\Iconneqt\Api\Rest\Iconneqt $iconneqt, $field)
	{
		parent::__construct($iconneqt, $field);

		$this->date = new \DateTime($field->date);
		$this->userid = $field->user;
		$this->default = $field->default;
		$this->required = $field->required;
		$this->settings = $field->settings;
		$this->role = $field->role;
	}

	public function getDate()
	{
		return $this->date;
	}

	public function getUserId()
	{
		return $this->userid;
	}

	public function getDefault()
	{
		return $this->default;
	}

	public function isRequired()
	{
		return $this->required;
	}

	public function getSettings()
	{
		return $this->settings;
	}

	public function getRole()
	{
		return $this->role;
	}

}
