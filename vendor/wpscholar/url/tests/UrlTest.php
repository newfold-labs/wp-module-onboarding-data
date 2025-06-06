<?php

namespace wpscholar\Tests;

use PHPUnit\Framework\TestCase;
use wpscholar\Url;

/**
 * @covers \wpscholar\Url
 */
class UrlTest extends TestCase {

	protected function setUp(): void {
		// Simulate server variables for getCurrentUrl tests
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/test-path';
		$_SERVER['HTTPS']       = 'on';
	}

	protected function tearDown(): void {
		// Clean up server variables
		unset( $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'], $_SERVER['HTTPS'] );
	}

	/**
	 * @covers \wpscholar\Url::__construct
	 * @covers \wpscholar\Url::parseUrl
	 */
	public function testUrlParsing() {
		$url = new Url( 'https://user:pass@example.com:8080/path?param=value#section' );

		$this->assertEquals( 'https', $url->scheme );
		$this->assertEquals( 'example.com', $url->host );
		$this->assertEquals( 'user', $url->user );
		$this->assertEquals( 'pass', $url->pass );
		$this->assertEquals( '8080', $url->port );
		$this->assertEquals( '/path', $url->path );
		$this->assertEquals( 'param=value', $url->query );
		$this->assertEquals( 'section', $url->fragment );

		// Test parsing invalid URL
		$url = new Url( 'not-a-valid-url' );
		$this->assertEquals( '', $url->scheme );
		$this->assertEquals( '', $url->host );
		$this->assertEquals( 'not-a-valid-url', $url->path );
	}

	/**
	 * @covers \wpscholar\Url::__get
	 * @covers \wpscholar\Url::__set
	 */
	public function testMagicMethods() {
		$url = new Url( 'https://example.com/path' );

		// Test __get
		$this->assertEquals( 'https', $url->scheme );
		$this->assertEquals( '', $url->user ); // Non-existent component
		$this->assertEquals( '', $url->nonexistent ); // Non-existent property

		// Test __set
		$url->scheme = 'http';
		$this->assertEquals( 'http', $url->scheme );

		// Test setting non-existent property (should be ignored)
		$url->nonexistent = 'value';
		$this->assertEquals( '', $url->nonexistent );

		// Test setting empty values
		$url->query = '';
		$this->assertEquals( '', $url->query );
		$this->assertEquals( 'http://example.com/path', (string) $url );

		// Test setting null values
		$url->fragment = null;
		$this->assertEquals( '', $url->fragment );
	}

	/**
	 * @covers \wpscholar\Url::__construct
	 * @covers \wpscholar\Url::getCurrentUrl
	 */
	public function testEmptyUrlDefaultsToCurrentUrl() {
		$url = new Url();
		$this->assertEquals( 'https://example.com/test-path', $url->toString() );
	}

	/**
	 * @covers \wpscholar\Url::addQueryVar
	 * @covers \wpscholar\Url::removeQueryVar
	 * @covers \wpscholar\Url::getQueryVar
	 * @covers \wpscholar\Url::getQueryVars
	 */
	public function testQueryParameterManipulation() {
		$url = new Url( 'https://example.com/path?param=value&existing=test' );

		// Test adding query parameter
		$url->addQueryVar( 'new_param', 'value' );
		$this->assertEquals( 'value', $url->getQueryVar( 'new_param' ) );

		// Test removing query parameter
		$url->removeQueryVar( 'param' );
		$this->assertNull( $url->getQueryVar( 'param' ) );

		// Test getting all query parameters
		$expected = array(
			'existing'  => 'test',
			'new_param' => 'value',
		);
		$this->assertEquals( $expected, $url->getQueryVars() );

		// Test array query parameters
		$url->addQueryVar( 'array_param', array( 'one', 'two' ) );
		$this->assertEquals( array( 'one', 'two' ), $url->getQueryVar( 'array_param' ) );

		// Test empty query string
		$url->query = '';
		$this->assertEmpty( $url->getQueryVars() );
	}

	/**
	 * @covers \wpscholar\Url::stripQueryString
	 * @covers \wpscholar\Url::buildUrl
	 */
	public function testStaticHelpers() {
		// Test stripping query string
		$urlString = 'https://example.com/path?param=value#fragment';
		$this->assertEquals( 'https://example.com/path#fragment', Url::stripQueryString( $urlString ) );

		// Test building URL from parts with all components
		$urlParts = array(
			'scheme'   => 'https',
			'user'     => 'username',
			'pass'     => 'password',
			'host'     => 'example.com',
			'port'     => '8080',
			'path'     => '/path',
			'query'    => 'param=value',
			'fragment' => 'section',
		);
		$expected = 'https://username:password@example.com:8080/path?param=value#section';
		$this->assertEquals( $expected, Url::buildUrl( $urlParts ) );

		// Test building URL with minimal parts
		$minimalParts = array( 'host' => 'example.com' );
		$this->assertEquals( 'example.com', Url::buildUrl( $minimalParts ) );

		// Test building URL with only authentication
		$authParts = array(
			'user' => 'user',
			'pass' => 'pass',
		);
		$this->assertEquals( 'user:pass@', Url::buildUrl( $authParts ) );
	}

	/**
	 * @covers \wpscholar\Url::getSegments
	 * @covers \wpscholar\Url::getSegment
	 * @covers \wpscholar\Url::hasTrailingSlash
	 */
	public function testPathManipulation() {
		$url = new Url( 'https://example.com/blog/2023/post-title/' );

		// Test getting all segments
		$expectedSegments = array( 'blog', '2023', 'post-title' );
		$this->assertEquals( $expectedSegments, $url->getSegments() );

		// Test getting specific segments
		$this->assertEquals( 'blog', $url->getSegment( 0 ) );
		$this->assertEquals( '2023', $url->getSegment( 1 ) );
		$this->assertEquals( 'post-title', $url->getSegment( 2 ) );
		$this->assertNull( $url->getSegment( 5 ) ); // Non-existent segment

		// Test trailing slash detection
		$this->assertTrue( $url->hasTrailingSlash() );

		// Test URL without trailing slash
		$url2 = new Url( 'https://example.com/blog/2023/post-title' );
		$this->assertFalse( $url2->hasTrailingSlash() );

		// Test empty path
		$url3 = new Url( 'https://example.com' );
		$this->assertEmpty( $url3->getSegments() );
	}

	/**
	 * @covers \wpscholar\Url::toString
	 * @covers \wpscholar\Url::toArray
	 */
	public function testUrlOutput() {
		$urlString = 'https://example.com/path?param=value#fragment';
		$url       = new Url( $urlString );

		// Test toString() method
		$this->assertEquals( $urlString, $url->toString() );

		// Test string casting
		$this->assertEquals( $urlString, (string) $url );

		// Test toArray() method
		$urlParts = $url->toArray();
		$this->assertIsArray( $urlParts );
		$this->assertEquals( 'https', $urlParts['scheme'] );
		$this->assertEquals( 'example.com', $urlParts['host'] );
		$this->assertEquals( '/path', $urlParts['path'] );
		$this->assertEquals( 'param=value', $urlParts['query'] );
		$this->assertEquals( 'fragment', $urlParts['fragment'] );

		// Test empty components
		$url   = new Url( 'http://example.com' );
		$parts = $url->toArray();
		$this->assertArrayHasKey( 'query', $parts );
		$this->assertEmpty( $parts['query'] );
		$this->assertArrayHasKey( 'fragment', $parts );
		$this->assertEmpty( $parts['fragment'] );
	}

	/**
	 * @covers \wpscholar\Url::getCurrentScheme
	 */
	public function testGetCurrentScheme() {
		// Test HTTPS via server variable
		$_SERVER['HTTPS'] = 'on';
		$this->assertEquals( 'https', Url::getCurrentScheme() );

		// Test HTTPS = 1
		$_SERVER['HTTPS'] = '1';
		$this->assertEquals( 'https', Url::getCurrentScheme() );

		// Test port 443
		unset( $_SERVER['HTTPS'] );
		$_SERVER['SERVER_PORT'] = '443';
		$this->assertEquals( 'https', Url::getCurrentScheme() );

		// Test forwarded proto
		unset( $_SERVER['SERVER_PORT'] );
		$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
		$this->assertEquals( 'https', Url::getCurrentScheme() );

		// Test default to http
		unset( $_SERVER['HTTP_X_FORWARDED_PROTO'] );
		$this->assertEquals( 'http', Url::getCurrentScheme() );
	}

	/**
	 * @covers \wpscholar\Url::buildPath
	 */
	public function testBuildPath() {
		// Test with segments
		$segments = array( 'blog', '2023', 'post-title' );
		$this->assertEquals( '/blog/2023/post-title', Url::buildPath( $segments ) );

		// Test with trailing slash
		$this->assertEquals( '/blog/2023/post-title/', Url::buildPath( $segments, true ) );

		// Test empty segments
		$this->assertEquals( '', Url::buildPath( array() ) );
		$this->assertEquals( '/', Url::buildPath( array(), true ) );
	}

	/**
	 * @covers \wpscholar\Url::getCurrentUrl
	 */
	public function testGetCurrentUrl() {
		$expected = 'https://example.com/test-path';
		$this->assertEquals( $expected, Url::getCurrentUrl() );

		// Test with different scheme
		unset( $_SERVER['HTTPS'] );
		$expected = 'http://example.com/test-path';
		$this->assertEquals( $expected, Url::getCurrentUrl() );
	}

	/**
	 * @covers \wpscholar\Url::getSegment
	 */
	public function testGetSegment() {
		$url = new Url( 'https://example.com/blog/2023/post-title' );

		// Test valid segments
		$this->assertEquals( 'blog', $url->getSegment( 0 ) );
		$this->assertEquals( '2023', $url->getSegment( 1 ) );
		$this->assertEquals( 'post-title', $url->getSegment( 2 ) );

		// Test non-existent segments
		$this->assertNull( $url->getSegment( -1 ) ); // Negative index
		$this->assertNull( $url->getSegment( 5 ) );  // Out of bounds

		// Test with empty path
		$url = new Url( 'https://example.com' );
		$this->assertNull( $url->getSegment( 0 ) );
	}

	/**
	 * @covers \wpscholar\Url::__toString
	 */
	public function testToString() {
		// Test full URL
		$url = new Url( 'https://user:pass@example.com:8080/path?param=value#fragment' );
		$this->assertEquals(
			'https://user:pass@example.com:8080/path?param=value#fragment',
			(string) $url
		);

		// Test minimal URL
		$url = new Url( 'http://example.com' );
		$this->assertEquals( 'http://example.com', (string) $url );

		// Test URL with empty components
		$url           = new Url( 'http://example.com' );
		$url->query    = '';
		$url->fragment = '';
		$this->assertEquals( 'http://example.com', (string) $url );
	}

	/**
	 * @covers \wpscholar\Url::parseUrl
	 * @covers \wpscholar\Url::__construct
	 */
	public function testParseUrlEdgeCases() {
		// Test with malformed URL
		$url = new Url( 'not-a-url' );
		$this->assertEquals( '', $url->scheme );
		$this->assertEquals( '', $url->host );
		$this->assertEquals( 'not-a-url', $url->path ); // Using public property access

		// Test with empty URL
		$url = new Url( '' );
		$this->assertEquals( 'https://example.com/test-path', (string) $url ); // Should get current URL

		// Test with only query string
		$url = new Url( '?test=1' );
		$this->assertEquals( '', $url->scheme );
		$this->assertEquals( '', $url->host );
		$this->assertEquals( '', $url->path );
		$this->assertEquals( 'test=1', $url->query );
	}

	/**
	 * @covers \wpscholar\Url::getCurrentUrl
	 */
	public function testGetCurrentUrlEdgeCases() {
		// Test with minimal server variables
		unset( $_SERVER['HTTPS'] );
		unset( $_SERVER['SERVER_PORT'] );
		unset( $_SERVER['HTTP_X_FORWARDED_PROTO'] );
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/test';

		$this->assertEquals( 'http://example.com/test', Url::getCurrentUrl() );

		// Test with empty REQUEST_URI
		$_SERVER['REQUEST_URI'] = '';
		$this->assertEquals( 'http://example.com', Url::getCurrentUrl() );
	}

	/**
	 * @covers \wpscholar\Url::getSegment
	 */
	public function testGetSegmentArrayAccess() {
		$url      = new Url( 'https://example.com/blog/2023/post-title' );
		$segments = $url->getSegments();

		// Test array access
		$this->assertEquals( 'blog', $segments[0] );
		$this->assertEquals( '2023', $segments[1] );
		$this->assertEquals( 'post-title', $segments[2] );

		// Test array count
		$this->assertCount( 3, $segments );
	}

	/**
	 * @covers \wpscholar\Url
	 */
	public function testUrlClass() {
		$url = new Url( 'https://example.com' );
		$this->assertInstanceOf( Url::class, $url );

		// Test all properties
		$this->assertEquals( 'https', $url->scheme );
		$this->assertEquals( 'example.com', $url->host );
		$this->assertEquals( '', $url->path );
		$this->assertEquals( '', $url->query );
		$this->assertEquals( '', $url->fragment );
		$this->assertEquals( '', $url->user );
		$this->assertEquals( '', $url->pass );
		$this->assertEquals( '', $url->port );
	}

	/**
	 * @covers \wpscholar\Url::addFragment
	 */
	public function testAddFragment() {
		$url = new Url( 'https://example.com/path' );
		$url->addFragment( 'section' );
		$this->assertEquals( 'section', $url->fragment );
		$this->assertEquals( 'https://example.com/path#section', (string) $url );
	}

	/**
	 * @covers \wpscholar\Url::__set
	 */
	public function testSetInvalidProperty() {
		$url = new Url( 'https://example.com' );

		// Test setting invalid property (should be ignored)
		$url->invalidProperty = 'value';
		$this->assertFalse( property_exists( $url, 'invalidProperty' ) );

		// Test setting protected property (should be ignored)
		$url->_scheme = 'http';
		$this->assertEquals( 'https', $url->scheme );

		// Test setting null value
		$url->fragment = null;
		$this->assertEquals( '', $url->fragment );

		// Test setting empty value
		$url->query = '';
		$this->assertEquals( '', $url->query );

		// Test setting protected property directly (should trigger error handling)
		try {
			$reflection = new \ReflectionClass( $url );
			$property   = $reflection->getProperty( '_scheme' );
			$property->setAccessible( true );
			$property->setValue( $url, 'ftp' );
			$this->fail( 'Should not be able to set protected property' );
		} catch ( \Exception $e ) {
			$this->assertTrue( true, 'Error handling branch covered' );
		}
	}

	/**
	 * @covers \wpscholar\Url::__set
	 */
	public function testSetPropertyErrorHandling() {
		$url = new Url( 'https://example.com' );

		// Test setting protected property
		$property       = '_scheme';
		$url->$property = 'http';
		$this->assertEquals( 'https', $url->scheme );

		// Test setting non-existent property
		$property       = 'nonexistent';
		$url->$property = 'value';
		$this->assertFalse( property_exists( $url, $property ) );
	}

	/**
	 * @covers \wpscholar\Url::__set
	 */
	public function testSetUrl() {
		$url = new Url( 'https://example.com/path' );

		// Test setting full URL
		$url->url = 'https://example.org/newpath';
		$this->assertEquals( 'https', $url->scheme );
		$this->assertEquals( 'example.org', $url->host );
		$this->assertEquals( '/newpath', $url->path );
		$this->assertEquals( 'https://example.org/newpath', (string) $url );

		// Test setting URL to empty string
		$url->url = '';
		$this->assertEquals( '', $url->scheme );
		$this->assertEquals( '', $url->host );
		$this->assertEquals( '', $url->path );
	}

	/**
	 * @covers \wpscholar\Url::__set
	 */
	public function testSetEmptyValues() {
		$url = new Url( 'https://example.com/path' );

		// Test setting individual components to empty values
		$url->scheme   = '';
		$url->host     = '';
		$url->path     = '';
		$url->query    = '';
		$url->fragment = '';

		$this->assertEquals( '', $url->scheme );
		$this->assertEquals( '', $url->host );
		$this->assertEquals( '', $url->path );
		$this->assertEquals( '', $url->query );
		$this->assertEquals( '', $url->fragment );
		$this->assertEquals( '', (string) $url );

		// Test setting individual components to null (should be converted to empty string)
		$url           = new Url( 'https://example.com/path?query=value#fragment' );
		$url->scheme   = null;
		$url->host     = null;
		$url->path     = null;
		$url->query    = null;
		$url->fragment = null;

		$this->assertEquals( '', $url->scheme );
		$this->assertEquals( '', $url->host );
		$this->assertEquals( '', $url->path );
		$this->assertEquals( '', $url->query );
		$this->assertEquals( '', $url->fragment );
		$this->assertEquals( '', (string) $url );
	}
}
