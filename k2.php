<?php
/**
 * @package    content
 *
 * @author     AkinaySau <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */


defined('_JEXEC') or die;

/**
 * Content plugin.
 *
 * @package  content
 * @since    1.0
 * @todo JPlugin->CMSPlugin for 4.0
 */
class plgSJSMK2 extends JPlugin {
	/**
	 * @var JDatabaseDriver
	 * @since 1.0
	 */
	protected $db;
	protected $autoloadLanguage = true;

	public function collectDataForSiteMap () {

		require_once JPATH_BASE . '/components/com_k2/helpers/route.php';
		$items = [];
		$items = array_merge($items, $this->getItems());
		$items = array_merge($items, $this->getCats());

		return $items;

	}

	protected function getItems () {
		$k2_items = $this->params->get('k2_items', 0);
		switch ( $k2_items ):
			case 1:
			case 2:
				$query = $this->db->getQuery(true);

				$query->select($this->db->quoteName([
					'id',
					'title',
					'modified',
					'catid',
				]));
				$query->from('#__k2_items');
				$k2_cats = $this->params->get('k2_items_cats');
				if ( $k2_items == 2 && count($k2_cats) ) {
					$query->where($this->db->quoteName('catid') . ' IN (' . implode(',', $this->db->quote($k2_cats)) . ')', 'OR');
					$query->andWhere($this->db->quoteName('published') . '= 1 ');
				} else {
					$query->where($this->db->quoteName('published') . '= 1 ');
				}
				$items = $this->db->setQuery($query)
					->loadObjectList();
				foreach ( $items as &$item ) {
					$loc     = SJSMHelper::getHost() . substr(JRoute::_(K2HelperRoute::getItemRoute($item->id)), 1);
					$lastmod = array_shift(explode(' ', $item->modified));
					$item    = SJSMHelper::urlClass($loc, $lastmod);
				}
				return $items;
				break;
			default:
				return [];
				break;
		endswitch;

	}

	protected function getCats () {
		$k2_cats = $this->params->get('k2_cats', 0);
		switch ( $k2_cats ):
			case 1:

				$query = $this->db->getQuery(true);

				$query->select($this->db->quoteName('id'))
					->from('#__k2_categories');
				$query->where($this->db->quoteName('published') . '= 1 ');
				$items = $this->db->setQuery($query)
					->loadObjectList();
				break;
			case 2:
				$items = $this->params->get('k2_cats_only');
				$items = array_chunk($items, 1);
				$items = array_map(function ( $i ) {
					$i[ 'id' ] = array_shift($i);

					return (object) $i;
				}, $items);
				break;
			default:
				return [];
				break;
		endswitch;

		foreach ( $items as &$item ) {
			$loc  = SJSMHelper::getHost() . substr(JRoute::_(K2HelperRoute::getCategoryRoute($item->id)), 1);
			$item = SJSMHelper::urlClass($loc);
		}

		return $items;
	}
}
