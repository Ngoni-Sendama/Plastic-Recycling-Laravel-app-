<?php

test('unknown api routes return a json 404', function () {
    $this->getJson('/api/does-not-exist')
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});
