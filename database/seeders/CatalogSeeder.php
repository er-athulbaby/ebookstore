<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookVariant;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Original placeholder catalog for RedCherry Books — not a copy of any real
 * publisher's titles. Swap this out for the real catalog via the admin
 * panel (or a proper import) before launch.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            'Fiction' => 'Novels and short stories across genres.',
            'Non-Fiction' => 'Essays, memoirs and narrative non-fiction.',
            'Business & Finance' => 'Strategy, money and the world of work.',
            'Self-Help' => 'Habits, mindset and personal growth.',
            'History' => 'Books on the past that shaped the present.',
            "Children's Books" => 'Stories for young readers.',
        ])->map(fn ($description, $name) => Category::updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'description' => $description]
        ));

        $authors = collect([
            'Meera Nair', 'Arjun Varma', 'Kavya Menon', 'Rohan Das',
            'Anjali Pillai', 'Vikram Suri', 'Leela Krishnan', 'Sam Thomas',
            'Divya Shenoy', 'Nikhil Rao',
        ])->mapWithKeys(fn ($name) => [$name => Author::updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'bio' => "{$name} is a RedCherry Books author."]
        )]);

        $books = [
            ['title' => 'The Monsoon Diaries', 'author' => 'Meera Nair', 'category' => 'Fiction', 'price' => 349, 'mrp' => 399, 'top100' => true, 'bestseller' => true, 'featured' => true],
            ['title' => 'Silence of the Backwaters', 'author' => 'Meera Nair', 'category' => 'Fiction', 'price' => 299, 'mrp' => 350],
            ['title' => 'Letters to a Younger Self', 'author' => 'Arjun Varma', 'category' => 'Non-Fiction', 'price' => 279, 'mrp' => 320, 'bestseller' => true],
            ['title' => 'The Founder\'s Dilemma', 'author' => 'Arjun Varma', 'category' => 'Business & Finance', 'price' => 449, 'mrp' => 549, 'top100' => true],
            ['title' => 'Atomic Focus', 'author' => 'Kavya Menon', 'category' => 'Self-Help', 'price' => 259, 'mrp' => 299, 'bestseller' => true, 'featured' => true],
            ['title' => 'The Quiet Discipline', 'author' => 'Kavya Menon', 'category' => 'Self-Help', 'price' => 229, 'mrp' => 270],
            ['title' => 'Empire of Spice', 'author' => 'Rohan Das', 'category' => 'History', 'price' => 399, 'mrp' => 450, 'top100' => true],
            ['title' => 'The Last Ledger', 'author' => 'Rohan Das', 'category' => 'History', 'price' => 349, 'mrp' => 399],
            ['title' => 'Grandmother\'s Kitchen', 'author' => 'Anjali Pillai', 'category' => 'Non-Fiction', 'price' => 319, 'mrp' => 360, 'featured' => true],
            ['title' => 'A River Called Home', 'author' => 'Anjali Pillai', 'category' => 'Fiction', 'price' => 289, 'mrp' => 330],
            ['title' => 'Markets, Myths and Money', 'author' => 'Vikram Suri', 'category' => 'Business & Finance', 'price' => 399, 'mrp' => 470, 'bestseller' => true],
            ['title' => 'The Compounding Mind', 'author' => 'Vikram Suri', 'category' => 'Business & Finance', 'price' => 359, 'mrp' => 420],
            ['title' => 'Verses for a Rainy Evening', 'author' => 'Leela Krishnan', 'category' => 'Fiction', 'price' => 199, 'mrp' => 230],
            ['title' => 'The Cartographer\'s Daughter', 'author' => 'Leela Krishnan', 'category' => 'Fiction', 'price' => 329, 'mrp' => 380, 'top100' => true],
            ['title' => 'Small Wins, Big Change', 'author' => 'Sam Thomas', 'category' => 'Self-Help', 'price' => 249, 'mrp' => 290, 'bestseller' => true],
            ['title' => 'The Twelve-Hour Rule', 'author' => 'Sam Thomas', 'category' => 'Self-Help', 'price' => 239, 'mrp' => 275],
            ['title' => 'Curie and the Comet', 'author' => 'Divya Shenoy', 'category' => "Children's Books", 'price' => 179, 'mrp' => 199, 'featured' => true],
            ['title' => 'The Brave Little Boatman', 'author' => 'Divya Shenoy', 'category' => "Children's Books", 'price' => 169, 'mrp' => 190],
            ['title' => 'Empire of Dust', 'author' => 'Nikhil Rao', 'category' => 'History', 'price' => 429, 'mrp' => 499, 'top100' => true],
            ['title' => 'The Long Return', 'author' => 'Nikhil Rao', 'category' => 'Fiction', 'price' => 309, 'mrp' => 350, 'bestseller' => true],
        ];

        foreach ($books as $i => $data) {
            /** @var Category $category */
            $category = $categories[$data['category']];
            $slug = Str::slug($data['title']);

            $book = Book::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'title' => $data['title'],
                    'description' => "\"{$data['title']}\" is published by RedCherry Books. Replace this placeholder description with real back-cover copy before launch.",
                    'publisher' => 'RedCherry Books',
                    'isbn' => '978'.str_pad((string) (1000000000 + $i), 10, '0', STR_PAD_LEFT),
                    'edition' => '1',
                    'pages' => fake()->numberBetween(160, 420),
                    'language' => 'English',
                    'published_at' => now()->subDays(fake()->numberBetween(5, 900)),
                    'is_featured' => $data['featured'] ?? false,
                    'is_bestseller' => $data['bestseller'] ?? false,
                    'is_top100' => $data['top100'] ?? false,
                    'status' => 'published',
                ]
            );

            $book->authors()->sync([$authors[$data['author']]->id]);

            $priceP = $data['price'] * 100;
            $mrpP = $data['mrp'] * 100;

            BookVariant::updateOrCreate(
                ['sku' => $slug.'-pb'],
                [
                    'book_id' => $book->id, 'type' => 'paperback',
                    'price' => $priceP, 'compare_at_price' => $mrpP,
                    'stock_quantity' => fake()->numberBetween(0, 60), 'weight_grams' => 300,
                    'is_active' => true,
                ]
            );

            // Roughly two-thirds of titles also get an ebook edition, priced ~30% below print.
            if ($i % 3 !== 0) {
                BookVariant::updateOrCreate(
                    ['sku' => $slug.'-eb'],
                    [
                        'book_id' => $book->id, 'type' => 'ebook',
                        'price' => (int) round($priceP * 0.7), 'compare_at_price' => (int) round($mrpP * 0.7),
                        'ebook_format' => 'pdf',
                        // No real file attached yet — active so you can exercise the full
                        // cart/checkout/download UI, but EbookDownloadController will 404
                        // on the actual file until you upload one via the admin panel.
                        'ebook_file_path' => null,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
