<?php

namespace Chillu\ViewCount\Extensions;

use Chillu\ViewCount\Model\ViewCount;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DB;

/**
 * Extension should be applied to any viewable DataObject subclass
 * in SilverStripe. If applied to custom controllers (not extending from ContentController),
 * the {@link trackCount()} method needs to be invoked manually.
 */
class ViewCountableExtension extends DataExtension
{

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab(
            'Root.Main',
            ReadonlyField::create('ViewCount', 'View Counts', $this->ViewCount()->Count)
        );
    }
    
    /**
     * @todo Should really be split into a separate controller extension,
     * but SS doesn't have extension points for init() there...
     */
    public function contentcontrollerInit()
    {
        $this->trackViewCount();
    }

    /**
     * @return ViewCount|Void
     */
    public function trackViewCount()
    {
        // Don't track crawlers and bots
        $bots = Config::inst()->get('Chillu\ViewCount\Extensions\ViewCountableExtension', 'bots');
        foreach ($bots as $bot) {
            if (stripos($bot, $_SERVER["HTTP_USER_AGENT"]) !== false) {
                return;
            }
        }

        // Don't track draft views
        if ($this->owner->hasExtension('SilverStripe\Versioned\Versioned') && !$this->owner->isPublished()) {
            return;
        }

        // Only track once per session
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();

        $tracked = $session->get('ViewCountsTracked');
        if ($tracked && array_key_exists($this->owner->ID, $tracked)) {
            return;
        }
        $tracked[$this->owner->ID] = true;
        $session->set('ViewCountsTracked', $tracked);

        // Track in DB
        DB::query(sprintf(
            'INSERT INTO "ViewCount" ("Count", "RecordID", "RecordClass") '
            . 'VALUES (1, %d, \'%s\') ON DUPLICATE KEY UPDATE "Count"="Count"+1',
            $this->owner->ID,
            addslashes($this->owner->ClassName)
        ));
    }

    /**
     * Return an existing or new ViewCount record.
     *
     * @return ViewCount
     */
    public function ViewCount()
    {
        $data = array(
            'RecordID' => $this->owner->ID,
            'RecordClass' => addslashes($this->owner->ClassName)
        );
        $count = ViewCount::get()->filter($data)->First();
        if (!$count) {
            $count = new ViewCount();
            $count->update($data);
        }
        
        return $count;
    }
}
