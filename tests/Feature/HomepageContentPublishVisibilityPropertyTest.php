<?php

namespace Tests\Feature;

use App\Models\HomepageContent;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageContentPublishVisibilityPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 48: Content publish visibility
     * 
     * For any homepage content, when published, it should appear in the homepage content list.
     * 
     * **Validates: Requirements 11.3**
     */
    public function test_content_publish_visibility_property(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string(),
            Generator\int(0, 100)
        )
        ->then(function ($title, $description, $order) {
            // Create unpublished content first
            $content = HomepageContent::factory()->create([
                'title' => $title,
                'description' => $description,
                'order' => $order,
                'is_published' => false,
            ]);

            // Verify content is NOT in published list initially
            $publishedContent = HomepageContent::published()->get();
            $this->assertFalse(
                $publishedContent->contains('id', $content->id),
                "Unpublished content should NOT appear in published list"
            );

            // Publish the content
            $content->update(['is_published' => true]);

            // Verify content IS in published list after publishing
            $publishedContentAfter = HomepageContent::published()->get();
            $this->assertTrue(
                $publishedContentAfter->contains('id', $content->id),
                "Published content should appear in published list"
            );

            // Verify the content is actually published
            $this->assertTrue(
                $content->fresh()->isPublished(),
                "Content isPublished() method should return true"
            );
        });
    }

    /**
     * Property 48 (Extended): Multiple published contents all appear in list
     * 
     * For any set of published homepage contents, all should appear in the homepage content list.
     * 
     * **Validates: Requirements 11.3**
     */
    public function test_multiple_published_contents_all_visible_property(): void
    {
        $this->forAll(
            Generator\int(1, 10)
        )
        ->then(function ($count) {
            // Create multiple published contents
            $publishedContents = HomepageContent::factory()
                ->count($count)
                ->published()
                ->create();

            // Get all published content from the database
            $retrievedPublished = HomepageContent::published()->get();

            // Verify all created published contents appear in the list
            foreach ($publishedContents as $content) {
                $this->assertTrue(
                    $retrievedPublished->contains('id', $content->id),
                    "Published content with ID {$content->id} should appear in published list"
                );
            }

            // Verify the count matches (at least the ones we created)
            $this->assertGreaterThanOrEqual(
                $count,
                $retrievedPublished->count(),
                "Published list should contain at least {$count} items"
            );
        });
    }

    /**
     * Property 48 (Inverse): Unpublished content never appears in published list
     * 
     * For any homepage content that is unpublished, it should NOT appear in the homepage content list.
     * 
     * **Validates: Requirements 11.3**
     */
    public function test_unpublished_content_never_visible_property(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string()
        )
        ->then(function ($title, $description) {
            // Create unpublished content
            $unpublishedContent = HomepageContent::factory()->create([
                'title' => $title,
                'description' => $description,
                'is_published' => false,
            ]);

            // Get all published content
            $publishedContent = HomepageContent::published()->get();

            // Verify unpublished content does NOT appear in published list
            $this->assertFalse(
                $publishedContent->contains('id', $unpublishedContent->id),
                "Unpublished content should NOT appear in published list"
            );

            // Verify isPublished returns false
            $this->assertFalse(
                $unpublishedContent->isPublished(),
                "Unpublished content isPublished() should return false"
            );
        });
    }

    /**
     * Property 48 (State Transition): Publishing changes visibility state
     * 
     * For any homepage content, changing is_published from false to true
     * should make it appear in the published list.
     * 
     * **Validates: Requirements 11.3**
     */
    public function test_publishing_changes_visibility_state_property(): void
    {
        $this->forAll(
            Generator\bool()
        )
        ->then(function ($initialPublishState) {
            // Create content with initial publish state
            $content = HomepageContent::factory()->create([
                'is_published' => $initialPublishState,
            ]);

            // Get initial visibility
            $initiallyVisible = HomepageContent::published()->where('id', $content->id)->exists();

            // Verify initial state matches expectation
            $this->assertEquals(
                $initialPublishState,
                $initiallyVisible,
                "Initial visibility should match initial publish state"
            );

            // Toggle the publish state
            $newPublishState = !$initialPublishState;
            $content->update(['is_published' => $newPublishState]);

            // Get new visibility
            $nowVisible = HomepageContent::published()->where('id', $content->id)->exists();

            // Verify new state matches expectation
            $this->assertEquals(
                $newPublishState,
                $nowVisible,
                "Visibility after toggle should match new publish state"
            );
        });
    }

    /**
     * Property 48 (Idempotence): Publishing already published content maintains visibility
     * 
     * For any published homepage content, setting is_published to true again
     * should maintain its visibility in the published list.
     * 
     * **Validates: Requirements 11.3**
     */
    public function test_republishing_maintains_visibility_property(): void
    {
        $this->forAll(
            Generator\string()
        )
        ->then(function ($title) {
            // Create published content
            $content = HomepageContent::factory()->create([
                'title' => $title,
                'is_published' => true,
            ]);

            // Verify it's visible
            $this->assertTrue(
                HomepageContent::published()->where('id', $content->id)->exists(),
                "Published content should be visible"
            );

            // "Republish" by setting is_published to true again
            $content->update(['is_published' => true]);

            // Verify it's still visible
            $this->assertTrue(
                HomepageContent::published()->where('id', $content->id)->exists(),
                "Content should remain visible after republishing"
            );

            // Verify isPublished still returns true
            $this->assertTrue(
                $content->fresh()->isPublished(),
                "isPublished() should still return true"
            );
        });
    }
}
