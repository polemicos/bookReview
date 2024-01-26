<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BookConroller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(){

    }
    public function index(Request $request): View
    {
        $title = $request->input('title');
        $filter = $request->input('filter', '');

        $books = Book::when($title, fn($query, $title)
            => $query->title($title));

        $books = match($filter){
            'popular_last_month' => $books->popularLastMonth(),
            'popular_last_6months' => $books->popularLast6Months(),
            'highest_rated_last_month' => $books->highestRateLastMonth(),
            'highest_rated_last_6months' => $books->highestRateLast6Months(),
            default => $books->latest()->withAvgRate()->withReviewsCount()
        };

        //$books = $books->get();

        $cacheKey = 'books:' . $filter . ':' . $title;
        $books = cache()->remember(
            $cacheKey,
            3600,
            fn() =>
            $books->get());


        return view('books.index', ['books' => $books]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id): View
    {

        $cacheKey = 'books:' . $id;

        $book = cache()->remember(
            $cacheKey, 3600,
            fn () => Book::with([
            'reviews' => fn ($query) => $query->latest()
        ])->withAvgRate()->withReviewsCount()->findOrFail($id)
        );

        return view('books.show', ['book' => $book]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
