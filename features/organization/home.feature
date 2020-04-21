Feature:
    In order to manage an organization,
    As an admin of an organization,
    I must be able to log in to my organization.

    Scenario: As anonymous, I cannot go to the homepage of an organization
        When I go to "/organizations/1"
        Then I should be on "/login"
        And the response status code should be 200

    Scenario: As an admin of an organization, I can go to the homepage of my organization
        Given I am authenticated as "john.doe@resop.com"
        And I am on the homepage
        When I follow "Gérer ma structure"
        Then I should be on "/organizations/1"
        And the response status code should be 200
        And I should see "DT75"

    Scenario Outline: As an admin of an organization but without a password, I cannot go to any page of my organization
        Given I am authenticated as "jane.doe@resop.com"
        When I go to "<url>"
        Then the response status code should be 403
        Examples:
            | url                     |
            | /organizations/3        |
            | /organizations/3/new    |
            | /organizations/3/search |
            | /organizations/3/edit   |
            | /organizations/3/assets |
            | /organizations/3/users  |
            | /organizations/planning |

    Scenario Outline: As an admin of an organization, I cannot go to the homepage of another organization
        Given I am authenticated as "john.doe@resop.com"
        When I go to "<url>"
        Then the response status code should be 403
        Examples:
            | url              |
            | /organizations/2 |
            | /organizations/3 |
            | /organizations/4 |
