<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Frontend\Rss\Controller;

use Gm;
use Gm\Helper\Url;
use Gm\Http\Response;
use Gm\Frontend\Rss\Model\Feed;
use Gm\Mvc\Controller\Controller;
use Gm\Exception\ModuleNotDefinedException;

/**
 * Контроллер вывода ленты (канала).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Frontend\Rss\Controller
 * @since 1.0
 */
class IndexController extends Controller
{
    /**
     * Действие "index" выводит указанный в запросе ленту (канал).
     * 
     * @return Response|string
     */
    public function indexAction(): Response|string
    {
        /** @var string $name Формат ленты (канала) */
        $format = Feed::FORMAT_RSS;
        /** @var string $channel Идентификатор ленты (канала) */
        $channel = Gm::$app->router->get('slug', '');
        // если канал не указан явно
        if (empty($channel)) {
            /** @var string $extension Расширение файла (foobar.atom => atom) */
            $extension = Gm::$app->urlManager->getExtension();
            if ($extension === 'atom') {
                $channel = Gm::$app->urlManager->getBasename();
                $format = Feed::FORMAT_ATOM;
            }
        }
        // если не удалось определить идентификатор канала
        if (empty($channel)) {
            return $this->render('@theme:views/pages/404');
        }

        // проверка кэша ленты
        if (Feed::hasCache($channel, $format)) {
            /** @var false|string $cache */
            $cache = Feed::getCache($channel, $format);
            if ($cache !== false) {
                /** @var Response $response */
                $response = $this->getResponse(Response::FORMAT_XML);
                return $response->setContent($cache);
            } else {
                Gm::debug('Error', ['error' => 'Cannot read cache from file "' . Feed::getCacheFile($channel, $format) . '"']);
            }
        }

        /** @var string $feedName Имя формата ленты (канала): 'RSS' => 'RssFeed' */
        $feedName = Feed::getFeedName($format);

        /** @var null|\Gm\Backend\RssFeeds\Model\Feed $dataFeed */
        $dataFeed = Gm::$app->modules->getModel($feedName, 'gm.be.rss_feeds');
        if ($dataFeed === null) {
            if (GM_MODE_PRO)
                return $this->render('@theme:views/pages/404');
            else {
                Gm::debug('Error', ['error' => sprintf('Cannot read model "%s" from module "%s"', $feedName, 'gm.be.rss_feeds')]);
                throw new ModuleNotDefinedException('gm.be.rss_feeds');
            }
        }

        /** @var null|\Gm\Backend\RssFeeds\Model\Feed $dataFeed */
        $dataFeed = $dataFeed->get($channel);
        // если канал существует и доступен
        if ($dataFeed && $dataFeed->isEnabled()) {
            /** @var \Gm\Frontend\Rss\Model\Feed $feed */
            $feed = $this->getModel($feedName, [
                'published'  => $dataFeed->published,
                'channel'    => $channel,
                'caching'    => $dataFeed->caching,
                'language'   => $dataFeed->getLanguage(),
                'url'        => Url::to(Gm::alias('@requestUri')),
                'constructs' => $dataFeed->getConstructs(),
                'items'      => $dataFeed->getItems()
            ]);

            /** @var Response $response */
            $response = $this->getResponse(Response::FORMAT_XML);
            return $response->setContent($feed->run());
        } else
            return $this->render('@theme:views/pages/404');
    }
}
