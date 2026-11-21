export interface LegalSubsection {
  title: string;
  paragraphs: string[];
  items?: string[];
}

export interface LegalSection {
  id: string;
  title: string;
  paragraphs?: string[];
  items?: string[];
  subsections?: LegalSubsection[];
}

export interface LegalDocument {
  eyebrow: string;
  title: string;
  intro: string;
  effectiveLabel: string;
  updatedLabel: string;
  effectiveDate: string;
  updatedDate: string;
  tocLabel: string;
  notice: string;
  sections: LegalSection[];
}

export const privacyDocument: LegalDocument = {
  eyebrow: "Legal",
  title: "Privacy Policy",
  intro:
    "This Privacy Policy explains what information Nova Backups collects and processes when you use our web panel and backup management services, how it is protected, and the choices you have.",
  effectiveLabel: "Effective date",
  updatedLabel: "Last updated",
  effectiveDate: "11 November 2026",
  updatedDate: "21 November 2026",
  tocLabel: "On this page",
  notice:
    "This document reflects the operation of Nova Backups as of the date above and is provided for transparency. Statutory rights that cannot be waived under applicable law continue to apply.",
  sections: [
    {
      id: "introduction",
      title: "Introduction",
      paragraphs: [
        'This Privacy Policy applies to the Nova Backups control panel, authentication portal, and related backup orchestration services (together, the "Service").',
        "Nova Backups is developed and maintained by Nova Ardiansyah. When you create an account or manage backup configurations through the panel, we process only the information necessary to authenticate your access and operate your scheduled backup jobs.",
        "This Policy does not apply to third-party storage providers, databases, or external infrastructure you connect to Nova Backups. Those services are governed by their own terms and privacy policies.",
      ],
    },
    {
      id: "information-we-collect",
      title: "Information We Collect",
      paragraphs: [
        "We collect only the minimal data required to provide secure access and manage your backup operations.",
      ],
      subsections: [
        {
          title: "Account and authentication information",
          paragraphs: [
            "When you register or sign in, we store your email address, display name, verification status, and an optional hashed password. One-Time Passwords (OTPs) generated for email verification, passwordless login, or password reset are stored temporarily in cache and automatically expire.",
          ],
        },
        {
          title: "Session and security logs",
          paragraphs: [
            "To maintain authenticated sessions and protect against abuse, our server processes standard technical metadata such as session identifiers, IP address, user agent, and OTP rate-limiting counters.",
          ],
        },
        {
          title: "What we do not sell or share",
          paragraphs: ["We do not sell, rent, or trade your personal data. Specifically, we do not:"],
          items: [
            "Use third-party advertising trackers or behavioral profiling scripts.",
            "Share your email address with marketing partners.",
            "Access or inspect the contents of your backup archives beyond automated job execution.",
          ],
        },
      ],
    },
    {
      id: "how-we-use-information",
      title: "How We Use Information",
      paragraphs: [
        "We use your account information solely to deliver and secure the Service:",
      ],
      items: [
        "Deliver OTP verification codes and security notifications to your email address.",
        "Authenticate your access to the Client Area and persist your session preferences.",
        "Enforce server-side rate limiting to prevent unauthorized access and brute-force attempts.",
        "Display and update your profile preferences inside the Client Area.",
      ],
    },
    {
      id: "data-retention-and-security",
      title: "Data Retention and Security",
      paragraphs: [
        "Passwords, when configured, are stored using strong one-way cryptographic hashing. OTP codes are valid for 10 minutes and are deleted from temporary cache immediately upon verification or expiration.",
        "We apply principle-of-least-privilege controls and encrypted transport (HTTPS/TLS) for data transmitted between your browser and the panel.",
      ],
    },
    {
      id: "your-rights",
      title: "Your Choices and Rights",
      paragraphs: [
        "You can review and update your display name and password settings at any time from the Profile section in the Client Area. If you wish to delete your account or inquire about your stored data, please contact the service administrator.",
      ],
    },
  ],
};

export const termsDocument: LegalDocument = {
  eyebrow: "Legal",
  title: "Terms of Service",
  intro:
    "These Terms of Service govern your access to and use of the Nova Backups control panel and associated backup management tools. By creating an account or signing in, you agree to these terms.",
  effectiveLabel: "Effective date",
  updatedLabel: "Last updated",
  effectiveDate: "11 November 2026",
  updatedDate: "21 November 2026",
  tocLabel: "On this page",
  notice:
    "Please read these terms carefully before using Nova Backups. Continued use of the Service after updates constitutes acceptance of the revised terms.",
  sections: [
    {
      id: "acceptance-of-terms",
      title: "Acceptance of Terms",
      paragraphs: [
        'By registering for or accessing Nova Backups (the "Service"), you confirm that you are authorized to manage the systems and data connected to your account and that you agree to be bound by these Terms of Service.',
      ],
    },
    {
      id: "accounts-and-security",
      title: "Accounts and Security",
      paragraphs: [
        "Access to the Service requires a valid email address verified via One-Time Password (OTP). You may optionally configure a password for your account from onboarding or the Client Area.",
        "You are responsible for maintaining the security of your email inbox, password, and active browser sessions. Never share OTP verification codes with anyone.",
      ],
    },
    {
      id: "acceptable-use",
      title: "Acceptable Use",
      paragraphs: ["When using the Service, you agree not to:"],
      items: [
        "Attempt to bypass authentication, OTP rate limiting, or server-side access controls.",
        "Use the Service to store, transfer, or back up unlawful content.",
        "Interfere with the availability, integrity, or security of the panel or underlying infrastructure.",
      ],
    },
    {
      id: "backups-and-responsibility",
      title: "Backups and User Responsibility",
      paragraphs: [
        "Nova Backups helps orchestrate and manage backup workflows, but you remain solely responsible for verifying that your backup schedules, storage destinations, and restoration procedures meet your operational requirements.",
        "We strongly recommend periodically testing backup restorations to ensure data integrity.",
      ],
    },
    {
      id: "limitation-of-liability",
      title: "Limitation of Liability",
      paragraphs: [
        'The Service is provided on an "as-is" and "as-available" basis without warranties of any kind, express or implied. To the fullest extent permitted by law, Nova Backups and its maintainer shall not be liable for any indirect, incidental, or consequential damages resulting from data loss or service interruption.',
      ],
    },
    {
      id: "changes-to-terms",
      title: "Changes to These Terms",
      paragraphs: [
        "We may update these Terms of Service as Nova Backups evolves. When changes are made, the updated effective date will be reflected at the top of this page.",
      ],
    },
  ],
};
