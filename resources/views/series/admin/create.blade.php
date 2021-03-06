@extends('layouts.form_page')

@section('pageTitle', 'New series')

@section('title', 'Create a new series')

@section('form')

    <form action="/series" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}

        @component('partials.text_input', ['name' => 'title', 'required' => true])
            Title
        @endcomponent

        @component('partials.textarea', ['name' => 'description', 'required' => true])
            Description
        @endcomponent

        @component('partials.file-input', ['name' => 'poster'])
            Choose a poster...
        @endcomponent

        <div class="columns">

            @component('partials.number_input',
                        ['name' => 'start_year', 'required' => true, 'fieldClass' => 'column'])
                Start year
            @endcomponent

            @component('partials.number_input',
                        ['name' => 'end_year', 'required' => false, 'fieldClass' => 'column'])
                End year
            @endcomponent

        </div>

        <seasons-list></seasons-list>

        <div class="field is-grouped">
            <p class="control">
                <button type="submit" class="button is-primary">
                    Create
                </button>
            </p>
            <p class="control">
                <a class="button is-link" href="/series">
                    Cancel
                </a>
            </p>
        </div>
    </form>

@endsection
