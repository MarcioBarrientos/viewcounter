<?php
class ViewCountableExtensionTest extends FunctionalTest {

	protected $usesDatabase = true;

	static $fixture_file = 'ViewCountableExtensionTest.yml';

	static $use_draft_site = true;

	protected $requiredExtensions = array(
		'Page' => array(
			'ViewCountableExtension'
		),
	);

	public function testViewCountTracksOncePerSession() {
		$page1 = $this->objFromFixture('Page', 'page1');
		$page2 = $this->objFromFixture('Page', 'page2');

		$response = $this->get($page1->RelativeLink());
		var_dump($response);
		$response = $this->get($page1->RelativeLink());
		$page1 = Page::get()->byID($page1->ID);
		$page2 = Page::get()->byID($page2->ID);
		$this->assertEquals(1, $page1->ViewCount()->Count, 'Doesnt double track');
		$this->assertNull($page2->ViewCount(), 'Doesnt track other pages');

		// TODO Fix 404s
		// $response = $this->get($page2->RelativeLink());
		// $this->session()->inst_clearAll();
		// $response = $this->get($page2->RelativeLink());
		// $this->session()->inst_clearAll();
		// $page2 = Page::get()->byID($page2->ID);
		// $this->assertEquals(2, $page2->ViewCount()->Count, 'Tracks for individual sessions');
	}

}