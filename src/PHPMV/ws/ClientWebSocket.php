<?php
namespace PHPMV\ws;

use PHPMV\js\JavascriptUtils;

class ClientWebSocket {

	private string $variableName = 'ws';

	private string $url = 'ws://127.0.0.1';

	private array $protocols;

	private array $events = [];

	public function __construct(string $serverName, int $port = 2346, array $protocols = []) {
		$this->url = "$serverName:$port";
		$this->protocols = $protocols;
	}

	public function on(string $event, string $jsCallback): self {
		$this->events = $jsCallback;
		return $this;
	}

	public function onMessage(string $jsCallback): self {
		return $this->on('message', $jsCallback);
	}

	public function onOpen(string $jsCallback): self {
		return $this->on('open', $jsCallback);
	}

	public function onError(string $jsCallback): self {
		return $this->on('error', $jsCallback);
	}

	public function onClose(string $jsCallback): self {
		return $this->on('close', $jsCallback);
	}

	public function send($data): string {
		if (\is_array($data) || \is_object($data)) {
			$data = JavascriptUtils::toJSON($data);
		}
		return $this->variableName . ".send('$data')";
	}

	public function compile(): string {
		$constructor = \sprintf('new WebSocket("%s",%s)', $this->url, \json_encode($this->protocols));
		$result = JavascriptUtils::declareVariable('let', $this->variableName, $constructor);
		foreach ($this->events as $event => $jsCallback) {
			$result .= $this->variableName . ".addEventListener('$event', " . JavascriptUtils::generateFunction($jsCallback, [
				'event'
			]) . ');' . PHP_EOL;
		}
		return $result;
	}

	public function __toString() {
		return JavascriptUtils::wrapScript($this->compile());
	}
}

