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
class Subscriber extends AbstractResource
{

	private $id;
	private $email;
	private $date;
	private $code;
	private $confirmed;
	private $unsubscribed;
	private $bounced;
	private $listid;

	public function __construct(\Iconneqt\Api\Rest\Iconneqt $iconneqt, $subscriber)
	{
		parent::__construct($iconneqt);

		$this->id = $subscriber->id;
		$this->email = $subscriber->email;
		$this->date = new \DateTime($subscriber->date);
		$this->code = $subscriber->code;
		$this->confirmed = $subscriber->confirmed;
		$this->unsubscribed = $subscriber->unsubscribed;
		$this->bounced = $subscriber->bounced;
		$this->listid = $subscriber->list;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getDate()
	{
		return $this->date;
	}

	public function getConfirmationCode()
	{
		return $this->code;
	}

	public function isConfirmed()
	{
		return $this->confirmed;
	}

	public function isUnsubscribed()
	{
		return $this->unsubscribed;
	}

	public function isBounced()
	{
		return $this->bounced;
	}

	public function isActive()
	{
		return $this->confirmed && !$this->unsubscribed && !$this->bounced;
	}

	public function getListId()
	{
		return $this->listid;
	}

	public function getList()
	{
		return $this->iconneqt->getList($listid);
	}
	
	public function getFields()
	{
		return $this->iconneqt->getSubscriberFields($this->id);
	}
	

}
