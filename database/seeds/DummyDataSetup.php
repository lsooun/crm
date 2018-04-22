<?php

use App\Models\User;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class DummyDataSetup extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    if (\App::environment() === 'local') {

      // Delete existing data


      Eloquent::unguard();

      //disable foreign key check for this connection before running seeders
      DB::statement('SET FOREIGN_KEY_CHECKS=0;');

      // Truncate all tables, except migrations
      $tables = array_except(DB::select('SHOW TABLES'), ['migrations']);
      foreach ($tables as $table) {
        $tables_in_database = "Tables_in_" . Config::get('database.connections.mysql.database');
        DB::table($table->$tables_in_database)->truncate();
      }

      // supposed to only apply to a single connection and reset it's self
      // but I like to explicitly undo what I've done for clarity
      DB::statement('SET FOREIGN_KEY_CHECKS=1;');

      //get roles
      $this->call('AccountSeeder');
      $this->call('CountrySeeder');
      $this->call('StateSeeder');
      $this->call('CitySeeder');
      $this->call('OptionSeeder');
      $this->call('TagSeeder');
      $this->call('PrintTemplateSeeder');
      $this->call('SettingsSeeder');


      if (empty($web_installer)) {
        $admin = Sentinel::registerAndActivate(array(
          'email' => 'admin@mw.life',
          'password' => "admin",
          'first_name' => 'Admin',
          'last_name' => 'Doe',
        ));
        $admin->user_id = $admin->id;
        $admin->save();

        $adminRole = Sentinel::findRoleById(1);
        $adminRole->users()->attach($admin);
      } else {
        $admin = Sentinel::findById(1);
      }

      //add dummy staff and customer
      $staff = Sentinel::registerAndActivate(array(
        'email' => 'staff@mw.life',
        'password' => "staff",
        'first_name' => 'Staff',
        'last_name' => 'Doe',
      ));
      $admin->users()->save($staff);

      foreach ($this->getPermissions() as $permission) {
        $staff->addPermission($permission);
      }
      $staff->save();

      $customer = Sentinel::registerAndActivate(array(
        'email' => 'customer@mw.life',
        'password' => "customer",
        'first_name' => 'Customer',
        'last_name' => 'Doe',
      ));
      Customer::create(array('user_id' => $customer->id, 'belong_user_id' => $staff->id));
      $staff->users()->save($customer);

      //add respective roles

      $staffRole = Sentinel::findRoleById(2);
      $staffRole->users()->attach($staff);
      $customerRole = Sentinel::findRoleById(3);
      $customerRole->users()->attach($customer);


      DB::table('sales_teams')->truncate();
      DB::table('opportunities')->truncate();

      //Delete existing seeded users except the first 4 users
      User::where('id', '>', 3)->get()->each(function ($user) {
        $user->forceDelete();
      });

      //Get the default ADMIN
      $user = User::find(1);
      $staffRole = Sentinel::getRoleRepository()->findByName('staff');
      $customerRole = Sentinel::getRoleRepository()->findByName('customer');

      //Seed Sales teams for default ADMIN
      foreach (range(1, 4) as $j) {
        $this->createSalesTeam($user->id, $j, $user);
        $this->createOpportunity($user, $user->id, $j);
      }


      //Get the default STAFF
      $staff = User::find(2);
      $this->createSalesTeam($staff->id, 1, $staff);
      $this->createSalesTeam($staff->id, 2, $staff);

      //Seed Sales teams for each STAFF
      foreach (range(1, 4) as $j) {
        $this->createSalesTeam($staff->id, $j, $staff);
        $this->createOpportunity($staff, $staff->id, $j);
      }

      foreach (range(1, 3) as $i) {
        $staff = $this->createStaff($i);
        $user->users()->save($staff);
        $staffRole->users()->attach($staff);

        $customer = $this->createCustomer($i);
        $staff->users()->save($customer);
        $customerRole->users()->attach($customer);
        $customer->customer()->save(factory(\App\Models\Customer::class)->make());


        //Seed Sales teams for each STAFF
        foreach (range(1, 5) as $j) {
          $this->createSalesTeam($staff->id, $j, $staff);
          $this->createOpportunity($staff, $i, $j);
        }

      }

      //finally call it installation is finished
      file_put_contents(storage_path('installed'), 'Welcome to Zxiaoke.CN');
    }
  }


  /**
   * @param $i
   * @param $j
   * @param $staff
   */
  private function createSalesTeam($i, $j, $staff) {
    $salesTeam = factory(\App\Models\Salesteam::class)->make([
      'salesteam' => 'STeam - ' . $i . ' - ' . $j,
      'team_leader' => $staff->id
    ]);
    return $staff->salesteams()->save($salesTeam);
  }

  /**
   * @param $i
   * @return mixed
   */
  private function createStaff($i) {
    $staff = Sentinel::registerAndActivate(array(
      'email' => 'staff' . $i . '@mw.life',
      'password' => "staff",
      'first_name' => 'Staff',
      'last_name' => $this->convertNumberToWord($i),
    ));
    return $staff;
  }

  /**
   * @param $staff
   * @param $i
   * @param $j
   */
  private function createOpportunity($staff, $i, $j) {
    $opprtunity = $staff->opportunities()->save(factory(\App\Models\Opportunity::class)->make([
      'opportunity' => 'Opp ' . $i . ' - ' . $j,
      'stages' => array_rand($this->stages())
    ]));

    return $opprtunity;
  }

  private function stages() {
    return [
      'New' => 'New',
      'Qualification' => 'Qualification',
      'Proposition' => 'Proposition',
      'Negotiation' => 'Negotiation',
      'Won' => 'Won',
      'Lost' => 'Lost',
      'Dead' => 'Dead',
    ];
  }

  private function convertNumberToWord($num = false) {
    $num = str_replace(array(',', ' '), '', trim($num));
    if (!$num) {
      return false;
    }
    $num = (int)$num;
    $words = array();
    $list1 = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
      'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
    );
    $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
    $list3 = array('', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
      'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
      'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
    );
    $num_length = strlen($num);
    $levels = (int)(($num_length + 2) / 3);
    $max_length = $levels * 3;
    $num = substr('00' . $num, -$max_length);
    $num_levels = str_split($num, 3);
    for ($i = 0; $i < count($num_levels); $i++) {
      $levels--;
      $hundreds = (int)($num_levels[$i] / 100);
      $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ($hundreds == 1 ? '' : 's') . ' ' : '');
      $tens = (int)($num_levels[$i] % 100);
      $singles = '';
      if ($tens < 20) {
        $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '');
      } else {
        $tens = (int)($tens / 10);
        $tens = ' ' . $list2[$tens] . ' ';
        $singles = (int)($num_levels[$i] % 10);
        $singles = ' ' . $list1[$singles] . ' ';
      }
      $words[] = $hundreds . $tens . $singles . (($levels && ( int )($num_levels[$i])) ? ' ' . $list3[$levels] . ' ' : '');
    } //end for loop
    $commas = count($words);
    if ($commas > 1) {
      $commas = $commas - 1;
    }
    return implode(' ', $words);
  }

  /**
   * @return mixed
   */
  private function createCustomer($i) {
    //$customer = factory(User::class)->make();
    $customer = Sentinel::registerAndActivate(array(
      'email' => 'customer' . $i . '@mw.life',
      'password' => "customer",
      'first_name' => 'Customer',
      'last_name' => $this->convertNumberToWord($i),
    ));

    return $customer;
  }

  /**
   * @return array
   *
   */
  private function getPermissions() {
    return [
      'sales_team.read',
      'sales_team.write',
      'sales_team.delete',
      'leads.read',
      'leads.write',
      'leads.delete',
      'opportunities.read',
      'opportunities.write',
      'opportunities.delete',
      'logged_calls.read',
      'logged_calls.write',
      'logged_calls.delete',
      'meetings.read',
      'meetings.write',
      'meetings.delete',
      'products.read',
      'products.write',
      'products.delete',
      'quotations.read',
      'quotations.write',
      'quotations.delete',
      'sales_orders.read',
      'sales_orders.write',
      'sales_orders.delete',
      'invoices.read',
      'invoices.write',
      'invoices.delete',
      'customers.read',
      'customers.write',
      'customers.delete',
      'contracts.read',
      'contracts.write',
      'contracts.delete',
      'staff.read',
      'staff.write',
      'staff.delete',
    ];
  }
}
