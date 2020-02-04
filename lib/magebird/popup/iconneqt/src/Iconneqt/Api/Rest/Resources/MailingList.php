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

require_once('AbstractResource.php');

class MailingList extends AbstractResource
{

	private $id;
	private $name;
	private $date;
	private $userid;

	public function __construct(\Iconneqt\Api\Rest\Iconneqt $iconneqt, $list)
	{
		parent::__construct($iconneqt);

		$this->id = $list->id;
		$this->name = $list->name;
		$this->date = new \DateTime($list->date);
		$this->userid = $list->user;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getDate()
	{
		return $this->date;
	}

	public function getUserId()
	{
		return $this->userid;
	}

	public function getFields()
	{
		return $this->iconneqt->getListFields($this->id);
	}

	public function getSubscribers()
	{
		return $this->iconneqt->getListSubscribers($this->id);
	}

	public function getFieldCount()
	{
		return $this->iconneqt->getListFieldCount($this->id);
	}

	public function getSubscriberCount()
	{
		return $this->iconneqt->getListSubscriberCount($this->id);
	}
	
	public function addSubscriber($email, $is_confirmed = true, $fields = []) {
		$subscriber = [
			'emailaddress'	=> $email,			
			'fields' => $fields,
		];
		
		if (!$is_confirmed) {
			$subscriber = array_merge($subscriber, [
				'confirmed' => false,
				'confirmdate' => null,
				'confirmip' => null,
			]);
		}
		
		$result = $this->iconneqt->postListSubscriber($this->id, $subscriber);
		
		return $this->iconneqt->getListSubscriber($this->id, $result->subscriberid);
	}

	public function getSubscriber($subscriber)
	{
		return $this->iconneqt->getListSubscriber($this->id, $subscriber);
	}	
	
	public function hasSubscriber($subscriber) {
		return $this->iconneqt->getListSubscriber($this->id, $subscriber);
	}
}
