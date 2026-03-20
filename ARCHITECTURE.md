# Architecture: laminas-feed

## Purpose
A PHP library for reading and writing Atom and RSS feeds, with PubSubHubbub (WebSub) subscriber/publisher support and a rich extension system for iTunes, Dublin Core, podcast namespaces, and more.

## Directory Structure
```
src/
  Reader/
    Reader.php                  # Static facade — import($uri) or importString($str)
    Feed/Atom.php / Feed/Rss.php
    Entry/Atom.php / Entry/Rss.php
    Extension/                  # Per-namespace extension readers
      Atom/ DublinCore/ Content/ Podcast/ ITunes/ GooglePlayPodcast/ PodcastIndex/ etc.
    Http/                       # Pluggable HTTP client (laminas-http or PSR-7)
    Extension_Manager.php       # Registry of installed reader extensions
  Writer/
    Feed.php                    # Feed builder
    Entry.php                   # Entry builder
    Renderer/
      Feed/ Atom.php / Rss.php  # Renders Feed to DOMDocument
      Entry/ Atom.php / Rss.php
    Extension/                  # Per-namespace writer extensions (iTune, DublinCore, PodcastIndex)
  PubSubHubbub/
    Publisher.php               # Notifies hub(s) of feed updates
    Subscriber.php              # Manages hub subscriptions (subscribe/unsubscribe/verify)
    Subscriber/Callback.php     # PSR-7 callback handler for hub verification & delivery
    Model/Subscription.php      # Subscription persistence model
  Exception/                    # Typed exceptions
  Uri.php                       # Thin URI wrapper
```

## Key Design Decisions
- **Extension registry** — both reader and writer use plugin managers to register namespace-specific extensions. Extensions are automatically applied when their XML namespace is detected.
- **DOMDocument-based I/O** — the writer renders to a `DOMDocument` tree and serializes to XML; the reader parses via `SimpleXMLElement`/`DOMDocument` internally.
- **Pluggable HTTP client** — the reader accepts a `Client_Interface` wrapper, allowing integration with any HTTP library (Guzzle, laminas-http, or a PSR-18 client).
- **WebSub (PubSubHubbub)** — the `PubSubHubbub` subsystem is self-contained: publisher notifies hubs, subscriber handles hub handshake and content delivery via a callback endpoint.

## Extension Points
- Create a custom reader extension by implementing the extension interface and registering it in `Extension_Manager`.
- Implement `Client_Interface` to use a custom HTTP client for feed fetching.
- Implement `Subscription_Persistence_Interface` to store WebSub subscriptions in any backend.

## Dependency Flow
```
Reader::import($uri)
  └─ Http\Client_Interface → fetch XML
       └─ Feed\Atom or Feed\Rss (wraps DOMDocument)
            └─ Extension_Manager applies namespace extensions
                 └─ Entry[] with getTitle(), getDescription(), getAuthors(), etc.

Writer\Feed → Entry[] → Renderer\Feed → DOMDocument → saveXml()
```
