# JSON Structure Format

This document describes the complete JSON structure format supported by the TimeSplitters JSON Form bundle.

## 📋 Root Structure

```json
{
    "slug": "form-identifier",
    "title": "Form title (optional)",
    "description": "Form description (optional)",
    "sections": [
        // Array of sections
    ]
}
```

## 📦 Section Structure

```json
{
    "slug": "section-identifier",
    "title": "Section title",
    "description": "Section description (optional)",
    "categories": [
        // Array of categories
    ],
    "submit": {
        // Optional submit button configuration
        "label": "Button text",
        "class": "CSS classes",
        "attr": {
            // Additional HTML attributes
        }
    }
}
```

## 🏷️ Category Structure

```json
{
    "slug": "category-identifier", 
    "title": "Category title",
    "description": "Category description (optional)",
    "questions": [
        // Array of questions
    ]
}
```

## ❓ Question Structure

Each question has this basic structure:

```json
{
    "key": "field_name",
    "type": "field_type",
    "label": "Field label",
    "required": true,
    "placeholder": "Placeholder text (optional)",
    "help": "Help text (optional)",
    "attr": {
        // Additional HTML attributes
    },
    "dependencies": [
        // Question dependencies (optional)
    ],
    "constraints": [
        // Validation constraints (optional)
    ]
}
```

## 🎯 Supported Field Types

### Text Fields

#### Text
```json
{
    "key": "name",
    "type": "text",
    "label": "Full name",
    "required": true,
    "placeholder": "Enter your full name"
}
```

#### Email
```json
{
    "key": "email",
    "type": "email",
    "label": "Email address",
    "required": true,
    "placeholder": "example@domain.com"
}
```

#### Password
```json
{
    "key": "password",
    "type": "password",
    "label": "Password",
    "required": true,
    "help": "At least 8 characters"
}
```

#### Textarea
```json
{
    "key": "description",
    "type": "textarea",
    "label": "Description",
    "placeholder": "Describe yourself...",
    "attr": {
        "rows": 4
    }
}
```

#### URL
```json
{
    "key": "website",
    "type": "url",
    "label": "Website",
    "placeholder": "https://example.com"
}
```

#### Phone
```json
{
    "key": "phone",
    "type": "tel",
    "label": "Phone number",
    "placeholder": "+1 234 567 8900"
}
```

### Numeric Fields

#### Integer
```json
{
    "key": "age",
    "type": "integer",
    "label": "Age",
    "required": true,
    "attr": {
        "min": 0,
        "max": 120
    }
}
```

#### Number (decimal)
```json
{
    "key": "price",
    "type": "number",
    "label": "Desired price",
    "attr": {
        "step": "0.01",
        "min": "0"
    }
}
```

#### Range
```json
{
    "key": "satisfaction",
    "type": "range",
    "label": "Satisfaction level",
    "attr": {
        "min": 1,
        "max": 10,
        "step": 1
    }
}
```

#### Money
```json
{
    "key": "budget",
    "type": "money",
    "label": "Available budget",
    "currency": "USD"
}
```

### Date and Time Fields

#### Date
```json
{
    "key": "birth_date",
    "type": "date",
    "label": "Birth date",
    "widget": "single_text",
    "required": true
}
```

#### Time
```json
{
    "key": "appointment_time",
    "type": "time",
    "label": "Preferred appointment time",
    "widget": "single_text"
}
```

#### DateTime
```json
{
    "key": "appointment",
    "type": "datetime",
    "label": "Appointment date and time",
    "widget": "single_text"
}
```

### Choice Fields

#### Select (dropdown)
```json
{
    "key": "country",
    "type": "choice",
    "label": "Country of residence",
    "choices": {
        "us": "United States",
        "ca": "Canada",
        "uk": "United Kingdom",
        "fr": "France",
        "de": "Germany"
    },
    "placeholder": "Choose your country",
    "required": true
}
```

#### Radio buttons
```json
{
    "key": "gender",
    "type": "choice",
    "label": "Gender",
    "choices": {
        "m": "Male",
        "f": "Female",
        "nb": "Non-binary",
        "other": "Other",
        "not_specified": "Prefer not to say"
    },
    "expanded": true,
    "multiple": false,
    "required": true
}
```

#### Checkboxes (multiple choice)
```json
{
    "key": "interests",
    "type": "choice",
    "label": "Interests",
    "choices": {
        "sport": "Sports",
        "music": "Music",
        "reading": "Reading",
        "movies": "Movies",
        "travel": "Travel",
        "cooking": "Cooking",
        "technology": "Technology",
        "art": "Art"
    },
    "expanded": true,
    "multiple": true,
    "help": "Select all your interests"
}
```

#### Country
```json
{
    "key": "nationality",
    "type": "country",
    "label": "Nationality",
    "placeholder": "Select your nationality"
}
```

### Boolean Fields

#### Checkbox
```json
{
    "key": "accept_terms",
    "type": "checkbox",
    "label": "I accept the terms and conditions",
    "required": true,
    "help": "You must accept the terms to continue"
}
```

### File Fields

#### File Upload
```json
{
    "key": "document",
    "type": "file",
    "label": "Upload document",
    "help": "Accepted formats: PDF, DOC, DOCX (max 5MB)",
    "attr": {
        "accept": ".pdf,.doc,.docx"
    }
}
```

### Hidden Fields

#### Hidden
```json
{
    "key": "csrf_token",
    "type": "hidden",
    "value": "generated_token_value"
}
```

## 🔘 Submit Button Configuration

### Simple Button
```json
{
    "submit": {
        "label": "Submit"
    }
}
```

### Advanced Button
```json
{
    "submit": {
        "label": "Save Information",
        "class": "btn btn-success btn-lg w-100",
        "attr": {
            "data-confirm": "Are you sure?",
            "data-loading-text": "Saving..."
        }
    }
}
```

## 🔗 Question Dependencies

### Simple Dependency
```json
{
    "key": "professional_details",
    "type": "textarea",
    "label": "Professional details",
    "dependencies": [
        {
            "field": "employment_status",
            "value": "employed"
        }
    ]
}
```

### Multiple AND Dependencies
```json
{
    "key": "company_name",
    "type": "text",
    "label": "Company name",
    "dependencies": [
        {
            "field": "employment_status",
            "value": "employed"
        },
        {
            "field": "work_type",
            "value": "full_time"
        }
    ]
}
```

### Multiple OR Dependencies
```json
{
    "key": "income_source",
    "type": "choice",
    "label": "Income source",
    "choices": {
        "salary": "Salary",
        "freelance": "Freelance",
        "business": "Business",
        "investments": "Investments"
    },
    "dependencies": [
        {
            "field": "employment_status",
            "value": ["employed", "self_employed"],
            "operator": "OR"
        }
    ]
}
```

## ✅ Validation Constraints

### Required Field
```json
{
    "key": "email",
    "type": "email",
    "label": "Email",
    "required": true
}
```

### Length Constraints
```json
{
    "key": "username",
    "type": "text",
    "label": "Username",
    "constraints": [
        {
            "type": "Length",
            "options": {
                "min": 3,
                "max": 20,
                "minMessage": "Username must be at least 3 characters",
                "maxMessage": "Username cannot exceed 20 characters"
            }
        }
    ]
}
```

### Custom Validation
```json
{
    "key": "age",
    "type": "integer",
    "label": "Age",
    "constraints": [
        {
            "type": "Range",
            "options": {
                "min": 18,
                "max": 99,
                "notInRangeMessage": "Age must be between 18 and 99"
            }
        }
    ]
}
```

## 📝 Complete Example

Here's a complete example of a user registration form:

```json
{
    "slug": "user-registration",
    "title": "User Registration Form",
    "description": "Complete your registration to access our platform",
    "sections": [
        {
            "slug": "personal-info",
            "title": "Personal Information",
            "categories": [
                {
                    "slug": "identity",
                    "title": "Identity",
                    "questions": [
                        {
                            "key": "first_name",
                            "type": "text",
                            "label": "First name",
                            "required": true,
                            "placeholder": "Enter your first name"
                        },
                        {
                            "key": "last_name",
                            "type": "text",
                            "label": "Last name",
                            "required": true,
                            "placeholder": "Enter your last name"
                        },
                        {
                            "key": "email",
                            "type": "email",
                            "label": "Email address",
                            "required": true,
                            "placeholder": "example@domain.com"
                        },
                        {
                            "key": "birth_date",
                            "type": "date",
                            "label": "Birth date",
                            "required": true,
                            "widget": "single_text"
                        }
                    ]
                }
            ]
        },
        {
            "slug": "account-info",
            "title": "Account Information",
            "categories": [
                {
                    "slug": "credentials",
                    "title": "Login Credentials",
                    "questions": [
                        {
                            "key": "username",
                            "type": "text",
                            "label": "Username",
                            "required": true,
                            "constraints": [
                                {
                                    "type": "Length",
                                    "options": {
                                        "min": 3,
                                        "max": 20
                                    }
                                }
                            ]
                        },
                        {
                            "key": "password",
                            "type": "password",
                            "label": "Password",
                            "required": true,
                            "help": "At least 8 characters with numbers and letters"
                        }
                    ]
                }
            ]
        },
        {
            "slug": "preferences",
            "title": "Preferences",
            "categories": [
                {
                    "slug": "settings",
                    "title": "Account Settings",
                    "questions": [
                        {
                            "key": "newsletter",
                            "type": "checkbox",
                            "label": "Subscribe to newsletter",
                            "help": "Receive our latest updates and offers"
                        },
                        {
                            "key": "language",
                            "type": "choice",
                            "label": "Preferred language",
                            "choices": {
                                "en": "English",
                                "fr": "French",
                                "es": "Spanish",
                                "de": "German"
                            },
                            "required": true
                        }
                    ]
                }
            ],
            "submit": {
                "label": "Create Account",
                "class": "btn btn-primary btn-lg w-100 mt-4",
                "attr": {
                    "data-confirm": "Create your account with the provided information?"
                }
            }
        }
    ]
}
```

## 📚 Notes

- **Default submit button**: If no section has a submit button configured, a default "Submit" button with CSS class `btn btn-primary` is automatically added at the end of the form.
- **Dependencies**: Questions can be shown/hidden based on the values of other fields using the `dependencies` array.
- **Validation**: Use Symfony's validation constraints in the `constraints` array for advanced validation.
- **Internationalization**: The bundle doesn't handle translations internally. Manage translations in your application for maximum flexibility.
- **Field types**: All Symfony form field types are supported. If a type is not recognized, it defaults to `text`.
