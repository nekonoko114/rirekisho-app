<?php

namespace Database\Seeders;

use App\Models\Resume;
use App\Models\ResumeHistory;
use App\Models\ResumeLicense;
use App\Models\ResumeProfile;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ResumeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('ja_JP');

        for ($i = 0; $i < 50; $i++) {
            $name = $faker->lastName().' '.$faker->firstName();
            $furigana = $faker->kanaName();
            $birth = $faker->dateTimeBetween('-60 years', '-20 years')->format('Y-m-d');
            $gender = $faker->randomElement(['male', 'female', 'other']);
            $phone = $faker->numerify('0##-####-####');
            $contactPhone = $faker->numerify('0##-####-####');
            $postal = $faker->numerify('1##-####');
            $contactPostal = $faker->numerify('5##-####');

            $resume = Resume::create([
                'name' => $name,
                'furigana' => $furigana,
                'birth_date' => $birth,
                'gender' => $gender,
                'phone' => $phone,
                'contact_phone' => $contactPhone,
                'email' => $faker->safeEmail(),
                'address' => $faker->prefecture().$faker->city().$faker->streetAddress(),
                'address_postal' => $postal,
                'contact_address' => $faker->prefecture().$faker->city().$faker->streetAddress(),
                'contact_postal' => $contactPostal,
            ]);

            // add 1-4 histories (mix education and work)
            $histCount = \rand(1, 4);
            for ($h = 0; $h < $histCount; $h++) {
                $type = $h % 2 === 0 ? 'education' : 'work';
                $year = $faker->numberBetween(1980, 2024);
                $month = $faker->numberBetween(1, 12);
                ResumeHistory::create([
                    'resume_id' => $resume->id,
                    'year' => $year,
                    'month' => $month,
                    'type' => $type,
                    'description' => $type === 'education' ? $faker->company().' '.$faker->jobTitle() : $faker->company().' — '.$faker->jobTitle(),
                    'sort_order' => $h,
                ]);
            }

            // add 0-3 licenses
            $licCount = \rand(0, 3);
            for ($l = 0; $l < $licCount; $l++) {
                $year = $faker->numberBetween(1990, 2024);
                $month = $faker->numberBetween(1, 12);
                ResumeLicense::create([
                    'resume_id' => $resume->id,
                    'year' => $year,
                    'month' => $month,
                    'name' => $faker->word().' 資格',
                    'details' => $faker->sentence(),
                ]);
            }

            // profile
            ResumeProfile::create([
                'resume_id' => $resume->id,
                'motivation' => $faker->realText(100),
                'personal_requests' => $faker->realText(60),
            ]);
        }
    }
}
