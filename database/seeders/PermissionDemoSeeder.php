<?php

namespace Database\Seeders;

use App\Models\pic;
use Faker\Provider\ar_EG\Person;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;


class PermissionDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    
        // Create permissions
        Permission::create(['name' => 'view participant dashboard']);
        Permission::create(['name' => 'create participant payment']);

        Permission::create(['name' => 'view staff dashboard']);
        Permission::create(['name' => 'view program list']);
        Permission::create(['name' => 'view program details']);
        Permission::create(['name' => 'create class']);
        Permission::create(['name' => 'view class details']);
        Permission::create(['name' => 'create agenda']);
        Permission::create(['name' => 'create participant']);
        Permission::create(['name' => 'view participant details']);
        Permission::create(['name' => 'create documentation']);
        Permission::create(['name' => 'edit agenda']);
        Permission::create(['name' => 'edit class']);
        Permission::create(['name' => 'edit documentation']);
        Permission::create(['name' => 'view class list']);
        Permission::create(['name' => 'create general payment']);
        Permission::create(['name' => 'approve participant payment']);
        Permission::create(['name' => 'reject participant payment']);
        Permission::create(['name' => 'create general payment']);
        Permission::create(['name' => 'edit general payment']);
        Permission::create(['name' => 'edit participant payment']);

        Permission::create(['name' => 'view executive dashboard']);
        Permission::create(['name' => 'create tna']);
        Permission::create(['name' => 'edit tna']);
        Permission::create(['name' => 'view list tna']);
        Permission::create(['name' => 'delete tna']);
        Permission::create(['name' => 'view detail tna']);
        Permission::create(['name' => 'create category tna']);
        Permission::create(['name' => 'create program']);
        Permission::create(['name' => 'approve general payment']);
        Permission::create(['name' => 'reject general payment']);
        Permission::create(['name' => 'approve program']);
        Permission::create(['name' => 'reject program']);
        Permission::create(['name' => 'grant role']);

        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $pics = pic::where('pic_working_unit', 'Learning and Development')->get();
        $executive = Role::firstOrCreate(['name' => 'executive', 'guard_name' => 'web']);
        $participant = Role::firstOrCreate(['name' => 'participant', 'guard_name' => 'web']);
        $pic = Role::firstOrCreate(['name' => 'pic', 'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        
        
        foreach ($pics as $pic){
            $roleToAssign = null;

            switch($pic->pic_position) {
                case 'Manager':
                    $roleToAssign = $manager;
                    break;
                case 'Staff':
                    $roleToAssign = $pic;
                    break;
                case 'Participant':
                    $roleToAssign = $participant;
                    break;
                case 'Executive':
                    $roleToAssign = $executive;
                    break;
                case 'Super Admin':
                    $roleToAssign = $superadmin; // Assuming 'Admin' is treated as a PIC role
                    break;
                
                default:
                    echo "No role assigned for position: {$pic->pic_position}\n";
                    continue 2; // Skip to the next iteration if no role is assigned
            }

            $user = User::firstOrCreate(
                ['nik' => $pic->karyawan_nik],
                [
                    'email' => $pic->pic_name . '@example.com',
                    'password' => bcrypt('password123'), // Use a secure password
                ]
            );

            if(!$user->hasRole($roleToAssign->name)) {
                $user->assignRole($roleToAssign);
            }

            echo "Assigned role '{$roleToAssign->name}' to user with NIK: {$pic->karyawan_nik}\n";
        }

        // Assign permissions to roles
        $superadmin->givePermissionTo(Permission::all());
        $participant->givePermissionTo([
            'view participant dashboard',
            'create participant payment',
            'view class list',
            'view class'
        ]);
        $pic->givePermissionTo([
            'view staff dashboard',
            'view program list',
            'view program details',
            'create class',
            'view class details',
            'create agenda',
            'create participant',
            'view participant details',
            'create documentation',
            'edit agenda',
            'edit class',
            'edit documentation',
            'view class list',
            'create general payment',
            'edit general payment',
            'edit participant payment',
            'approve participant payment',
            'reject participant payment',
        ]);
        $executive->givePermissionTo([
            'view executive dashboard',
            'view list tna',
            'view detail tna',
            'view program list',
            'view program details',
            'view class list',
            'view class details',
            'approve general payment',
            'reject general payment',
            'approve program',
            'reject program'
        ]);
        $manager->givePermissionTo([
            'view executive dashboard',
            'create tna',
            'edit tna',
            'view list tna',
            'delete tna',
            'view detail tna',
            'create category tna',
            'create program',
            'approve general payment',
            'reject general payment',
            'view program list',
            'view program details',
            'create class',
            'view class details',
            'create agenda',
            'create participant',
            'view participant details',
            'create documentation',
            'edit agenda',
            'edit class',
            'edit documentation',
            'view class list',
            'create general payment',
            'approve participant payment',
            'reject participant payment'
        ]);
    
    }
}
