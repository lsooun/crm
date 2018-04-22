<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Efriandika\LaravelSettings\Facades\Settings;
use Sentinel;

class UserController extends Controller {
	protected $user;
	protected $non_read_meeages;
	protected $last_meeages;

	public function __construct() {
		$this->middleware( function ( $request, $next ) {
			if ( Sentinel::check() ) {
				$this->user = Sentinel::getUser();
				view()->share( 'user_data', $this->user );
				$this->non_read_meeages = Email::where( 'to', $this->user->id )->where( 'read', '0' )->count();
				view()->share( 'non_read_meeages', $this->non_read_meeages );
				$this->last_meeages = Email::where( 'to', $this->user->id )->limit( 5 )->get();
				view()->share( 'last_meeages', $this->last_meeages );

				view()->share( 'jquery_date', Settings::get( 'jquery_date' ) );
				view()->share( 'jquery_date_time', Settings::get( 'jquery_date_time' ) );

			} else {
				Sentinel::logout( null, true );

				return redirect( 'signin' )->send();
			}

			return $next( $request );
		} );
	}
}
