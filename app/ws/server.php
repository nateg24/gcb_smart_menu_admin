<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\SocketServer;
use React\Socket\ConnectionInterface as ReactConn;

final class MenuWs implements MessageComponentInterface {
  private \SplObjectStorage $clients;
  public function __construct() { $this->clients = new \SplObjectStorage(); }
  public function onOpen(ConnectionInterface $conn) { $this->clients->attach($conn); }
  public function onClose(ConnectionInterface $conn) { $this->clients->detach($conn); }
  public function onError(ConnectionInterface $conn, \Exception $e) { $conn->close(); }
  public function onMessage(ConnectionInterface $from, $msg) {}
  public function broadcast(array $data): void {
    $json = json_encode($data, JSON_UNESCAPED_SLASHES);
    foreach ($this->clients as $c) $c->send($json);
  }
}

$loop = LoopFactory::create();
$wsComponent = new MenuWs();

$wsPort = 8080;
$webSock = new SocketServer("0.0.0.0:$wsPort", [], $loop);
new IoServer(new HttpServer(new WsServer($wsComponent)), $webSock, $loop);

$internalPort = 8090;
$internalSock = new SocketServer("0.0.0.0:$internalPort", [], $loop);

$internalSock->on('connection', function (ReactConn $conn) use ($wsComponent) {
  $buf = '';
  $conn->on('data', function ($data) use (&$buf, $conn, $wsComponent) {
    $buf .= $data;
    if (strpos($buf, "\n") === false) return;

    $line = trim(strtok($buf, "\n"));
    $buf = '';

    $payload = json_decode($line, true);
    if (!is_array($payload)) { $conn->end(); return; }
    if (($payload['token'] ?? '') !== 'change-me') { $conn->end(); return; }

    if (($payload['type'] ?? '') === 'menu_updated') {
      $wsComponent->broadcast([
        'type'    => 'menu_updated',
        'menu_id' => (int)($payload['menu_id'] ?? 0),
        'version' => (int)($payload['version'] ?? 0),
      ]);
    }
    $conn->end();
  });
});

$loop->run();
