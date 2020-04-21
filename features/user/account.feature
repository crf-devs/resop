Feature:
    In order to update my account,
    As a user,
    I must be able to edit my personal information.

    Scenario: As anonymous, I cannot update an account
        When I go to "/user/edit"
        Then I should be on "/login"
        And the response status code should be 200

    Scenario: As a user, I can see my account
        Given I am authenticated as "john.doe@resop.com"
        When I go to "/user/edit"
        Then I should be on "/user/edit"
        And the response status code should be 200
        And the "user_identificationNumber" field should contain "990001A"
        And the "user_emailAddress" field should contain "john.doe@resop.com"
        And the "user_firstName" field should contain "John"
        And the "user_lastName" field should contain "DOE"
        And the "user_phoneNumber" field should contain "06 12 34 56 78"
        And the "user_birthday_day" field should contain "1"
        And the "user_birthday_month" field should contain "1"
        And the "user_birthday_year" field should contain "1990"
        And the "user_organizationOccupation" field should contain "Secouriste"
        And the "user[vulnerable]" field should contain "1"
        And the "user[fullyEquipped]" field should contain "1"
        And the "user[drivingLicence]" field should contain "1"
        And the "user[occupation][choice]" field should contain "Pharmacien"

    Scenario: As a user, I cannot update my account with empty data
        Given I am authenticated as "john.doe@resop.com"
        And I am on "/user/edit"
        When I fill in the following:
            | user[identificationNumber] |  |
            | user[emailAddress]         |  |
            | user[firstName]            |  |
            | user[lastName]             |  |
            | user[phoneNumber]          |  |
        And I press "Valider"
        Then I should be on "/user/edit"
        And the response status code should be 400
        And I should see "Cette valeur ne doit pas être vide." in the "label[for=user_identificationNumber] .form-error-message" element
        And I should see "Cette valeur ne doit pas être vide." in the "label[for=user_emailAddress] .form-error-message" element
        And I should see "Cette valeur ne doit pas être vide." in the "label[for=user_firstName] .form-error-message" element
        And I should see "Cette valeur ne doit pas être vide." in the "label[for=user_lastName] .form-error-message" element
        And I should see "Cette valeur ne doit pas être nulle." in the "label[for=user_phoneNumber] .form-error-message" element

    Scenario: As a user, I cannot update my account with invalid data
        Given I am authenticated as "john.doe@resop.com"
        And I am on "/user/edit"
        When I fill in the following:
            | user[identificationNumber] | invalid |
            | user[emailAddress]         | invalid |
            | user[phoneNumber]          | invalid |
        And I press "Valider"
        Then I should be on "/user/edit"
        And the response status code should be 400
        And I should see "Le format est invalide, exemple : 0123456789A." in the "label[for=user_identificationNumber] .form-error-message" element
        And I should see "Cette valeur n'est pas une adresse email valide." in the "label[for=user_emailAddress] .form-error-message" element
        And I should see "Cette valeur n'est pas un numéro de téléphone valide." in the "label[for=user_phoneNumber] .form-error-message" element

    Scenario Outline: As a user, I can update my account
        Given I am authenticated as "john.doe@resop.com"
        And I am on "/user/edit"
        When I fill in the following:
            | user[identificationNumber] | 899999A           |
            | user[emailAddress]         | vincent@resop.com |
            | user[phoneNumber]          | <phoneNumber>     |
        And I press "Valider"
        Then I should be on "/"
        And the response status code should be 200
        And I should see "Vos informations ont été mises à jour avec succès."
        When I follow "Déconnexion"
        And I fill in the following:
            | user_login[identifier]      | <login> |
            | user_login[birthday][day]   | 01      |
            | user_login[birthday][month] | 01      |
            | user_login[birthday][year]  | 1990    |
        And I press "Je me connecte"
        Then I should be on "/"
        And the response status code should be 200
        And I should see "NIVOL : 899999A"
        Examples:
            | login             | phoneNumber  |
            | vincent@resop.com | 0612345678   |
            | 899999A           | +33612345678 |
