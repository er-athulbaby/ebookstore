<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookImage;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Real RedCherry Books titles, transcribed from the printed covers (title, author,
 * cover price, year, ISBN). Cover art lives in storage/app/public/covers/{slug}.jpg.
 * Safe to re-run: books are matched on slug and updated in place.
 *
 * Not on the covers, so set to editable defaults: category (best guess from the
 * cover), stock (50 per title), no discount (price = printed MRP).
 */
class RedcherryTitlesSeeder extends Seeder
{
    private const DEFAULT_STOCK = 50;

    public function run(): void
    {
        $titles = [
            // [slug, title, malayalam title, authors, category, language, price ₹, year, isbn, edition]
            ['ashtavakra-geetha', 'Ashtavakra Geetha', 'അഷ്ടാവക്ര ഗീത', ['Venu V Desam'], 'Non-Fiction', 'Malayalam', 150, 2023, '9788195838332', null],
            ['balachandranmarude-maranam', 'Balachandranmarude Maranam', 'ബാലചന്ദ്രന്മാരുടെ മരണം', ['Aryad Balachandran'], 'Fiction', 'Malayalam', 150, 2023, '9788195838394', null],
            ['annapurna-devi', 'Annapurna Devi', 'അന്നപൂർണാദേവി', ['Venu V Desam'], 'Non-Fiction', 'Malayalam', 150, 2024, '9788195838363', null],
            ['governorude-helicopter', 'Governorude Helicopter', 'ഗവർണറുടെ ഹെലികോപ്റ്റർ', ['S K P Karuna'], 'Fiction', 'Malayalam', 300, 2026, '9788199881648', null],
            ['hiroshima-muthal-hangzhou-vare', 'Hiroshima Muthal Hangzhou Vare', 'ഹിരോഷിമ മുതൽ ഹാങ്ങ്ചോ വരെ', ['Sanil P Thomas'], 'Non-Fiction', 'Malayalam', 280, 2026, '9788196668105', '2nd Edition'],
            ['jalamkondu-murivettaval', 'Jalamkondu Murivettaval', 'ജലംകൊണ്ടു മുറിവേറ്റവൾ', ['Nisha Anilkumar'], 'Fiction', 'Malayalam', 130, 2024, '9788196668150', null],
            ['bhranthan', 'Bhranthan (The Madman)', 'ഭ്രാന്തൻ', ['Kahlil Gibran'], 'Fiction', 'Malayalam', 130, 2023, '9788195838356', null],
            ['kerala-paadangal', 'Kerala Paadangal', 'കേരള പാഠങ്ങൾ', ['Dr. P J Vincent'], 'History', 'Malayalam', 250, 2025, '9788196668129', null],
            ['lalleshwari', 'Lalleshwari', 'ലല്ലേശ്വരി', ['Venu V Desam'], 'Non-Fiction', 'Malayalam', 200, 2025, '9788198768995', null],
            ['mammootty-anubhavam-orma-padanam', 'Mammootty: Anubhavam Orma Padanam', 'മമ്മൂട്ടി: അനുഭവം ഓർമ പഠനം', ['Anilkumar Thiruvoth'], 'Non-Fiction', 'Malayalam', 300, 2026, '9788199881662', '2nd Edition'],
            ['kaladoothante-varavu', 'Kaladoothante Varavu', 'കാലദൂതന്റെ വരവ്', ['M T Raveendran'], 'Fiction', 'Malayalam', 150, 2025, '9788194606277', null],
            ['munpe-nadanna-oral', 'Munpe Nadanna Oral (The Forerunner)', 'മുൻപേ നടന്ന ഒരാൾ', ['Kahlil Gibran'], 'Fiction', 'Malayalam', 120, 2023, '9788195838370', '2nd Edition'],
            ['nilam', 'Nilam', 'നിലം', ['Bava Chelladurai'], 'Fiction', 'Malayalam', 270, 2026, '9788199881655', null],
            ['olive-marathanalil', 'Olive Marathanalil: A Journey Through Jordan', 'ഒലീവ് മരത്തണലിൽ', ['Ramesh Sankaran'], 'Non-Fiction', 'Malayalam', 250, 2026, '9788196668174', '3rd Edition'],
            ['maranendriyam', 'Maranendriyam', 'മരണേന്ദ്രിയം', ['V Jayadev'], 'Fiction', 'Malayalam', 250, 2025, '9788198768957', null],
            ['puzhaykku-oru-poovum-neerum', 'Puzhaykku Oru Poovum Neerum', 'പുഴയ്ക്ക് ഒരു പൂവും നീരും', ['M T Raveendran'], 'Fiction', 'Malayalam', 200, 2025, '9788197592706', null],
            ['security-and-loss-prevention', 'Security & Loss Prevention: A 360° Handbook', null, ['Aryad Balachandran'], 'Business & Finance', 'English', 400, 2026, '9788199881617', null],
            ['pravachakan', 'Pravachakan (The Prophet)', 'പ്രവാചകൻ', ['Kahlil Gibran'], 'Fiction', 'Malayalam', 150, 2023, '9788194606284', null],
            ['sonia-gandhi', 'Sonia Gandhi', 'സോണിയ ഗാന്ധി', ['Sanil P Thomas'], 'Non-Fiction', 'Malayalam', 250, 2025, '9788198768988', null],
            ['thottavante-dribbling', 'Thottavante Dribbling', 'തോറ്റവന്റെ ഡ്രിബ്ലിങ്ങ്', ['A P Sajisha'], 'Fiction', 'Malayalam', 175, 2026, '9788195838301', 'Revised Edition'],
            ['shihabuddeente-kavithakal', 'Shihabuddeente Kavithakal', 'ശിഹാബുദ്ദീന്റെ കവിതകൾ', ['Shihabuddin Poithumkadavu'], 'Fiction', 'Malayalam', 125, 2017, null, null],
        ];

        foreach ($titles as [$slug, $title, $localTitle, $authorNames, $categoryName, $language, $priceRupees, $year, $isbn, $edition]) {
            $category = Category::firstOrCreate(['name' => $categoryName], ['slug' => Str::slug($categoryName)]);

            $description = $localTitle
                ? "{$localTitle} — {$language} edition, published by RedCherry Books in {$year}."
                : "{$language} edition, published by RedCherry Books in {$year}.";

            $book = Book::updateOrCreate(['slug' => $slug], [
                'category_id' => $category->id,
                'title' => $title,
                'description' => $description,
                'publisher' => 'RedCherry Books',
                'isbn' => $isbn,
                'edition' => $edition,
                'language' => $language,
                'published_at' => "{$year}-01-01",
                'status' => 'published',
            ]);

            $authorIds = collect($authorNames)->map(function ($name) {
                $author = Author::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
                // Portraits cropped from the back covers; never overwrite one set in /admin.
                $photo = "authors/{$author->slug}.jpg";
                if (! $author->photo_path && is_file(storage_path("app/public/{$photo}"))) {
                    $author->update(['photo_path' => $photo]);
                }
                return $author->id;
            });
            $book->authors()->sync($authorIds);

            $book->variants()->updateOrCreate(['type' => 'paperback'], [
                'sku' => 'RCB-'.strtoupper($isbn ?? Str::slug($slug)).'-PB',
                'price' => $priceRupees * 100, // paise
                'compare_at_price' => null,
                'stock_quantity' => $book->variants()->where('type', 'paperback')->value('stock_quantity') ?? self::DEFAULT_STOCK,
                'is_active' => true,
            ]);

            BookImage::updateOrCreate(
                ['book_id' => $book->id, 'sort_order' => 0],
                ['path' => "covers/{$slug}.jpg", 'alt_text' => "{$title} cover"]
            );
        }
    }
}
