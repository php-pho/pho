<?php
namespace Pho\Http\Session;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class HmacCookieSessionStorage implements SessionStorageInterface {
    protected $started = false;
    protected $name = 'PHO_SESSION';
    protected $saveHandler;
    protected $bags = [];
    protected $data = [];
    protected $secret;
    protected $algorithm;

    public function __construct($secret = null, $algorithm = 'sha256') {
        $this->secret = $secret;
        $this->algorithm = $algorithm;
    }

    public function start(Request $request = null)
    {
        if ($this->started) {
            return true;
        }

        $this->loadSession($request);
    }

    public function isStarted()
    {
        return $this->started;
    }

    public function getId()
    {
        return true;
    }

    public function setId($id)
    {
        return true;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function regenerate($destroy = false, $lifetime = null)
    {
        return true;
    }

    public function save(Response $response = null)
    {
        $sessionData = [];

        foreach ($this->bags as $bag) {
            $key = $bag->getStorageKey();
            $sessionData[$key] = $bag->getBag()->all();
        }

        $sessionString = base64_encode(serialize($sessionData));
        $hmac = hash_hmac($this->algorithm, $sessionString, $this->secret);
        $cookieValue = sprintf("%s|%s", $sessionString, $hmac);

        $cookie = new Cookie($this->getName(), $cookieValue);
        $response->headers->setCookie($cookie);
    }

    public function clear()
    {
        foreach ($this->bags as $bag) {
            $bag->clear();
        }
    }

    public function getBag($name)
    {
        if (!isset($this->bags[$name])) {
            throw new \InvalidArgumentException(sprintf('The SessionBagInterface %s is not registered.', $name));
        }

        return $this->bags[$name];
    }

    public function registerBag(SessionBagInterface $bag)
    {
        if ($this->started) {
            throw new \LogicException('Cannot register a bag when the session is already started.');
        }

        $this->bags[$bag->getName()] = $bag;
    }

    public function setMetadataBag(MetadataBag $metaBag = null)
    {
        if (null === $metaBag) {
            $metaBag = new MetadataBag();
        }

        $this->metadataBag = $metaBag;
    }

    public function getMetadataBag()
    {
        return $this->metadataBag;
    }

    protected function loadSession(Request $request)
    {
        $this->data = $this->loadDataFromCookie($request);

        foreach ($this->bags as $bag) {
            $key = $bag->getStorageKey();
            $this->data[$key] = isset($this->data[$key]) ? $this->data[$key] : array();
            $bag->initialize($this->data[$key]);
        }

        $this->started = true;
    }

    protected function checkSignature($data, $signature) {
        return hash_hmac($this->algorithm, $data, $this->secret) == $signature;
    }

    protected function loadDataFromCookie(Request $request) {
        $cookieName = $this->getName();
        $cookieValue = $request->cookies->get($cookieName, null);

        if (!$cookieValue) {
            return ($this->data = []);
        }

        $parts = explode('|', $cookieValue, 2);
        $sessionData = $parts[0] ?? null;
        $signature = $parts[1] ?? null;

        if (!$this->checkSignature($sessionData, $signature)) {
            return [];
        }
        
        return unserialize(base64_decode($sessionData));
    }
}
