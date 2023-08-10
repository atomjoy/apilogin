<?php

namespace Atomjoy\Apilogin\Notifications\Contracts;

class NotifyMessage
{
	protected array $links = [];
	protected string $content = '';
	protected string $html = '';
	protected string $vue = '';

	function __construct(public $replace_links = true)
	{
	}

	function setContent($msg)
	{
		return $this->content = $msg;
	}

	function setLink($slug, $href,  $text)
	{
		if (!empty($slug) && !empty($href) && !empty($text)) {
			$this->links[] = [
				'slug' => $slug,
				'href' => $href,
				'text' => $text
			];
		}

		if ($this->replace_links == true) {
			$this->replaceLinks();
		}
	}

	protected function replaceLinks()
	{
		$this->vue = $this->content;
		$this->html = $this->content;

		foreach ($this->links as $link) {
			$this->vue = str_replace(
				$link['slug'],
				'<router-link :to="' . $link['href'] . '" class="notify-link">' . $link['text'] . '</router-link>',
				$this->vue
			);

			$this->html = str_replace(
				$link['slug'],
				'<a href="' . $link['href'] . '" class="notify-link">' . $link['text'] . '</a>',
				$this->html
			);
		}
	}

	function getContent()
	{
		return $this->content;
	}

	function getHtml()
	{
		return $this->html;
	}

	function getVue()
	{
		return $this->vue;
	}

	function getLinks()
	{
		return $this->links;
	}
}
