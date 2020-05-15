@profile
Feature:
    In order to update my account
    As a user
    I must be able to edit my personal information

    Scenario: As anonymous, I cannot update an account
        When I go to "/user/edit"
        Then I should be on "/login"

    Scenario: As a user, I can see my account
        Given I am authenticated as "admin201@resop.com"
        When I go to "/user/edit"
        Then the "user[identificationNumber]" field should contain "990001A"
        And the "user[emailAddress]" field should contain "admin201@resop.com"
        And the "user[firstName]" field should contain "John"
        And the "user[lastName]" field should contain "DOE"
        And the "user[phoneNumber]" field should contain "06 12 34 56 78"
        And the "user[birthday][day]" field should contain "1"
        And the "user[birthday][month]" field should contain "1"
        And the "user[birthday][year]" field should contain "1990"
        And the "user[properties][organizationOccupation]" field should contain "Secouriste"
        And the "user[properties][vulnerable]" field should contain "1"
        And the "user[properties][fullyEquipped]" field should contain "1"
        And the "user[properties][drivingLicence]" field should contain "1"
        And the "user[properties][occupation][choice]" field should contain "Pharmacien"

    Scenario: As a user, I cannot update my account with empty data
        Given I am authenticated as "admin201@resop.com"
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
        Given I am authenticated as "admin201@resop.com"
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

    Scenario: As a user, I can update my account
        Given I am authenticated as "admin201@resop.com"
        And I am on "/user/edit"
        When I fill in the following:
            | user[identificationNumber]               | 899999A                |
            | user[emailAddress]                       | vincent@resop.com      |
            | user[phoneNumber]                        | 0611111111             |
            | user[firstName]                          | firstName              |
            | user[lastName]                           | lastName               |
            | user[birthday][day]                      | 2                      |
            | user[birthday][month]                    | 2                      |
            | user[birthday][year]                     | 1980                   |
            | user[properties][organizationOccupation] | organizationOccupation |
            | user[properties][vulnerable]             | 0                      |
            | user[properties][fullyEquipped]          | 0                      |
            | user[properties][drivingLicence]         | 0                      |
            | user[properties][occupation][choice]     | Pompier                |
        And I press "Valider"
        Then I should be on "/"
        And the response status code should be 200
        And I should see "Vos informations ont été mises à jour avec succès."
        When I go to "/user/edit"
        Then the "user[identificationNumber]" field should contain "899999A"
        And the "user[emailAddress]" field should contain "vincent@resop.com"
        And the "user[firstName]" field should contain "firstName"
        And the "user[lastName]" field should contain "lastName"
        And the "user[phoneNumber]" field should contain "06 11 11 11 11"
        And the "user[birthday][day]" field should contain "2"
        And the "user[birthday][month]" field should contain "2"
        And the "user[birthday][year]" field should contain "1980"
        And the "user[properties][organizationOccupation]" field should contain "organizationOccupation"
        And the "user[properties][vulnerable]" field should contain "0"
        And the "user[properties][fullyEquipped]" field should contain "0"
        And the "user[properties][drivingLicence]" field should contain "0"
        And the "user[properties][occupation][choice]" field should contain "Pompier"

    @javascript
    Scenario: As a user, I can update my occupation with a free text
        Given I am authenticated as "admin201@resop.com"
        And I am on "/user/edit"
        When I check "Autre"
        Then I wait for "#user_properties_occupation_other" to be visible
        And I fill in "user[properties][occupation][other]" with "Plombier"
        And I press "Valider"
        Then I should be on "/"
        And I should see "Vos informations ont été mises à jour avec succès."
        When I go to "/user/edit"
        Then I wait for "#user_properties_occupation_other" to be visible
        And the "user[properties][occupation][other]" field should contain "Plombier"

    Scenario Outline: As a user, I can update my email and login using the new email
        Given I am authenticated as "admin201@resop.com"
        And I am on "/user/edit"
        When I fill in the following:
            | user[identificationNumber] | 899999A           |
            | user[emailAddress]         | vincent@resop.com |
            | user[phoneNumber]          | <phoneNumber>     |
        And I press "Valider"
        Then I should be on "/"
        And I should see "Vos informations ont été mises à jour avec succès."
        When I follow "Déconnexion"
        And I fill in the following:
            | user_login[identifier]      | <login> |
            | user_login[birthday][day]   | 01      |
            | user_login[birthday][month] | 01      |
            | user_login[birthday][year]  | 1990    |
        And I press "Je me connecte"
        Then I should be on "/"
        And I should see "NIVOL : 899999A"
        Examples:
            | login             | phoneNumber  |
            | vincent@resop.com | 0612345678   |
            | 899999A           | +33612345678 |
