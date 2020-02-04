<?php

namespace Iconneqt\Api\Rest;

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
require_once('Client/Client.php');
require_once('Resources/MailingList.php');
require_once('Resources/Subscriber.php');
class Iconneqt
{

	private $client;

	public function __construct($url, $username, $token)
	{
		$this->client = new Client\Client($url, $username, $token);
	}

	/**
	 * Get all Lists that can be accessed using the credentials used.
	 * @param integer $offset
	 * @param integer $limit
	 * @return \Iconneqt\Api\Rest\MailingList[]
	 */
	public function getLists($offset = 0, $limit = 100)
	{
		$lists = [];
		foreach ($this->client->get("lists?offset={$offset}&limit={$limit}") as $list) {
			$lists[] = new Resources\MailingList($this, $list);
		}
		return $lists;
	}

	/**
	 * Get the number of lists that can be accessed using the credentials used.
	 * @return integer
	 */
	public function getListCount()
	{
		return $this->client->get("lists/count");
	}

	/**
	 * Get a specific list.
	 * @param integer $list
	 * @return \Iconneqt\Api\Rest\MailingList|null
	 */
	public function getList($list)
	{
		$result = $this->client->get("lists/{$list}");
		return $result ? new Resources\MailingList($this, $result) : null;
	}

	/**
	 * Delete a specific list.
	 * @param integer $list
	 * @return boolean
	 */
	public function deleteList($list)
	{
		return !!$this->client->delete("lists/{$list}");
	}

	/**
	 * Get all fields that are associated with a list.
	 * @param integer $list
	 * @param integer $offset
	 * @param integer $limit
	 * @return \Iconneqt\Api\Rest\Resources\ListField[]
	 */
	public function getListFields($list, $offset = 0, $limit = 100)
	{
		$fields = [];
		foreach ($this->client->get("lists/{$list}/fields?offset={$offset}&limit={$limit}") as $field) {
			$fields[] = new Resources\ListField($this, $field);
		}
		return $fields;
	}

	/**
	 * Get a specific field for a list.
	 * @param integer $list
	 * @param integer $field
	 * @return \Iconneqt\Api\Rest\Resources\ListField|null
	 */
	public function getListField($list, $field)
	{
		$result = $this->client->get("lists/{$list}/fields/{$field}");
		return $result ? new Resources\ListField($this, $result) : null;
	}

	/**
	 * Delete a specific field from a list.
	 * This will not delete the field, but will break the association between
	 * the field and the list
	 * @param integer $list
	 * @param integer $field
	 * @return boolean
	 */
	public function deleteListField($list, $field)
	{
		return !!$this->client->delete("lists/{$list}/fields/{$field}");
	}

	/**
	 * Get the number of fields associated with a list
	 * @param integer $list
	 * @return integer
	 */
	public function getListFieldCount($list)
	{
		return $this->client->get("lists/{$list}/fields/count");
	}

	/*
	 * Get all fields that are associated with a list.
	 * @param integer $list
	 * @param integer $offset
	 * @param integer $limit
	 * @return \Iconneqt\Api\Rest\Resources\Subscriber[]
	 */
	public function getListSubscribers($list, $offset = 0, $limit = 100)
	{
		$subscribers = [];
		foreach ($this->client->get("lists/{$list}/subscribers?offset={$offset}&limit={$limit}&sort=date") as $subscriber) {
			$subscribers[] = new Resources\Subscriber($this, $subscriber);
		}
		return $subscribers;
	}

	/*
	 * Get all fields that are associated with a list.
	 * @param integer $list
	 * @param integer|string $subscriber
	 * @return \Iconneqt\Api\Rest\Resources\Subscriber|null
	 */
	public function getListSubscriber($list, $subscriber)
	{
		$result = $this->client->get("lists/{$list}/subscribers/{$subscriber}");
		return $result ? new Resources\Subscriber($this, $result) : null;
	}

	/*
	 * Get the number of subscribers on a list
	 * @param integer $list
	 * @return integer
	 */
	public function getListSubscriberCount($list)
	{
		return $this->client->get("lists/{$list}/subscribers/count");
	}

	/*
	 * Get all fields that are set for a subscriber.
	 * @param integer $subscriber
	 * @param integer $offset
	 * @param integer $limit
	 * @return \Iconneqt\Api\Rest\Resources\SubscriberField[]
	 */
	public function getSubscriberFields($subscriber, $offset = 0, $limit = 100)
	{
		$fields = [];
		foreach ($this->client->get("subscribers/{$subscriber}/fields?offset={$offset}&limit={$limit}") as $subscriber) {
			$fields[] = new Resources\SubscriberField($this, $subscriber);
		}
		return $fields;
	}

	/**
	 * Add a new subscriber to a list
	 * @param integer $list
	 * @param array $data
	 * @return boolean
	 */
	public function postListSubscriber($list, $data)
	{
  
		return $this->client->post("lists/{$list}/subscribers", $data);
	}

}
