<?php

declare(strict_types=1);

namespace App\Enums;

use jeremyaliparo\Foundation\Traits\HasEnumOptions;

enum Suffix: string
{
    use HasEnumOptions;

    /**
     * ==========================================
     * GENERATIONAL SUFFIXES
     * ==========================================
     */
    case JR = 'Jr.';
    case SR = 'Sr.';
    case III = 'III';
    case IV = 'IV';
    case V = 'V';
    case VI = 'VI';
    case VII = 'VII';
    case VIII = 'VIII';
    case IX = 'IX';
    case X = 'X';

    /**
     * ==========================================
     * ACADEMIC DEGREES (Doctoral)
     * ==========================================
     */
    case MD = 'M.D.';
    case PHD = 'Ph.D.';
    case EDD = 'Ed.D.';
    case DVM = 'D.V.M.';
    case DDS = 'D.D.S.';
    case DMD = 'D.M.D.';
    case JD = 'J.D.';
    case DRPH = 'DrPH';

    /**
     * ==========================================
     * ACADEMIC DEGREES (Master's)
     * ==========================================
     */
    case MA = 'M.A.';
    case MS = 'M.S.';
    case MSC = 'M.Sc.';
    case MBA = 'M.B.A.';
    case MPH = 'M.P.H.';
    case MAN = 'M.A.N.';
    case MSN = 'M.S.N.';
    case MPA = 'M.P.A.';

    /**
     * ==========================================
     * PROFESSIONAL LICENSES & CERTIFICATIONS
     * ==========================================
     */
    case RN = 'R.N.';
    case RMT = 'R.M.T.';
    case RPH = 'R.Ph.';
    case RND = 'R.N.D.';
    case CPA = 'CPA';
    case CE = 'CE';
    case PE = 'PE';
    case PTRP = 'PTRP';

    /**
     * ==========================================
     * MEDICAL FELLOWSHIPS & DIPLOMATES (PH Context)
     * ==========================================
     */
    case DPCP = 'DPCP';
    case FPCP = 'FPCP';
    case DPPS = 'DPPS';
    case FPPS = 'FPPS';
    case DPOGS = 'DPOGS';
    case FPOGS = 'FPOGS';
    case FPSGS = 'FPSGS';
    case FPAFP = 'FPAFP';

    /**
     * ==========================================
     * PHILIPPINE GOV'T EXECUTIVE RANKS (CES)
     * ==========================================
     */
    case CESO_I = 'CESO I';
    case CESO_II = 'CESO II';
    case CESO_III = 'CESO III';
    case CESO_IV = 'CESO IV';
    case CESO_V = 'CESO V';
    case CESO_VI = 'CESO VI';
    case CESE = 'CESE';

    public function description(): string
    {
        return match ($this) {
            self::JR => 'Junior',
            self::SR => 'Senior',
            self::III => 'III',
            self::IV => 'IV',
            self::V => 'V',
            self::VI => 'VI',
            self::VII => 'VII',
            self::VIII => 'VIII',
            self::IX => 'IX',
            self::X => 'X',

            self::MD => 'Doctor of Medicine',
            self::PHD => 'Doctor of Philosophy',
            self::EDD => 'Doctor of Education',
            self::DVM => 'Doctor of Veterinary Medicine',
            self::DDS => 'Doctor of Dental Surgery',
            self::DMD => 'Doctor of Dental Medicine',
            self::JD => 'Juris Doctor (Law)',
            self::DRPH => 'Doctor of Public Health',

            self::MA => 'Master of Arts',
            self::MS => 'Master of Science',
            self::MSC => 'Master of Science',
            self::MBA => 'Master of Business Administration',
            self::MPH => 'Master of Public Health',
            self::MAN => 'Master of Arts in Nursing',
            self::MSN => 'Master of Science in Nursing',
            self::MPA => 'Master of Public Administration',

            self::RN => 'Registered Nurse',
            self::RMT => 'Registered Medical Technologist',
            self::RPH => 'Registered Pharmacist',
            self::RND => 'Registered Nutritionist-Dietitian',
            self::CPA => 'Certified Public Accountant',
            self::CE => 'Civil Engineer',
            self::PE => 'Professional Engineer',
            self::PTRP => 'Philippine Registered Physical Therapist',

            self::DPCP => 'Diplomate, Philippine College of Physicians',
            self::FPCP => 'Fellow, Philippine College of Physicians',
            self::DPPS => 'Diplomate, Philippine Pediatric Society',
            self::FPPS => 'Fellow, Philippine Pediatric Society',
            self::DPOGS => 'Diplomate, Phil. Obstetrical & Gynecological Society',
            self::FPOGS => 'Fellow, Phil. Obstetrical & Gynecological Society',
            self::FPSGS => 'Fellow, Phil. Society of General Surgeons',
            self::FPAFP => 'Fellow, Phil. Academy of Family Physicians',

            self::CESO_I => 'Career Executive Service Officer Rank I',
            self::CESO_II => 'Career Executive Service Officer Rank II',
            self::CESO_III => 'Career Executive Service Officer Rank III',
            self::CESO_IV => 'Career Executive Service Officer Rank IV',
            self::CESO_V => 'Career Executive Service Officer Rank V',
            self::CESO_VI => 'Career Executive Service Officer Rank VI',
            self::CESE => 'Career Executive Service Eligible',
        };
    }
}
