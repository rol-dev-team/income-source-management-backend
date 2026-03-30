# Income Source Management API - Technical Documentation

This document provides route-wise technical documentation for the Income Source Management API, a Laravel-based application. All routes are listed with their HTTP methods, URIs, corresponding controller methods, and detailed descriptions of functionality.

## Authentication Routes (No Middleware)

These routes do not require authentication.

### POST /api/auth/register
- **Controller Method**: `AuthController@register`
- **Description**: Registers a new user by creating a User record with full_name, username, email, password (hashed), and role.
- **Parameters**:
  - `full_name` (string, required)
  - `username` (string, required)
  - `email` (string, required)
  - `password` (string, required)
  - `role` (string, required)
- **Response**: JSON with message "User created", status 201.

### POST /api/auth/login
- **Controller Method**: `AuthController@login`
- **Description**: Authenticates a user with username and password, generates JWT access token and refresh token.
- **Parameters**:
  - `username` (string, required)
  - `password` (string, required)
- **Response**: JSON with user data, access_token, refresh_token, token_type, expires_in.

### POST /api/auth/refresh
- **Controller Method**: `AuthController@refresh`
- **Description**: Refreshes the access token using a valid refresh token, rotates the refresh token.
- **Parameters**:
  - `refresh_token` (string, required)
- **Response**: JSON with new access_token, refresh_token, token_type, expires_in.

## Authenticated Routes (Require `auth:api` Middleware)

All following routes require a valid JWT token in the Authorization header.

### POST /api/auth/logout
- **Controller Method**: `AuthController@logout`
- **Description**: Invalidates the current JWT token and deletes associated refresh tokens for the user.
- **Parameters**: None (uses token from header)
- **Response**: JSON with message "Logged out successfully".

### GET /api/auth/profile
- **Controller Method**: `AuthController@profile`
- **Description**: Returns the authenticated user's profile data.
- **Parameters**: None
- **Response**: JSON with user object.

### User Management

#### GET /api/users
- **Controller Method**: `UserController@index`
- **Description**: Retrieves a list of all users.
- **Parameters**: None
- **Response**: JSON array of users.

#### POST /api/users
- **Controller Method**: `UserController@store`
- **Description**: Creates a new user.
- **Parameters**: User data (full_name, username, email, password, role)
- **Response**: JSON with created user.

#### PUT /api/users/{id}
- **Controller Method**: `UserController@update`
- **Description**: Updates an existing user by ID.
- **Parameters**: User data, ID in path
- **Response**: JSON with updated user.

#### DELETE /api/users/{id}
- **Controller Method**: `UserController@destroy`
- **Description**: Deletes a user by ID.
- **Parameters**: ID in path
- **Response**: JSON confirmation.

### Source Management

#### GET /api/sources
- **Controller Method**: `SourceController@index` and `SourceController@getAllSources` (duplicate routes)
- **Description**: Retrieves all sources.
- **Parameters**: None
- **Response**: JSON array of sources.

#### POST /api/sources
- **Controller Method**: `SourceController@store`
- **Description**: Creates a new source.
- **Parameters**: Source data
- **Response**: JSON with created source.

#### GET /api/sources/{id}
- **Controller Method**: `SourceController@show`
- **Description**: Retrieves a specific source by ID.
- **Parameters**: ID in path
- **Response**: JSON source object.

#### PUT /api/sources/{id}
- **Controller Method**: `SourceController@update`
- **Description**: Updates a source by ID.
- **Parameters**: Source data, ID in path
- **Response**: JSON updated source.

#### DELETE /api/sources/{id}
- **Controller Method**: `SourceController@destroy`
- **Description**: Deletes a source by ID.
- **Parameters**: ID in path
- **Response**: JSON confirmation.

### Expense Type Management

#### GET /api/expense-types
- **Controller Method**: `ExpenseTypeController@index`
- **Description**: Retrieves all expense types.
- **Parameters**: None
- **Response**: JSON array of expense types.

#### POST /api/expense-types
- **Controller Method**: `ExpenseTypeController@store`
- **Description**: Creates a new expense type.
- **Parameters**: Expense type data
- **Response**: JSON created expense type.

#### GET /api/expense-types/{id}
- **Controller Method**: `ExpenseTypeController@show`
- **Description**: Retrieves an expense type by ID.
- **Parameters**: ID in path
- **Response**: JSON expense type.

#### GET /api/expense-types-by-source/{id}
- **Controller Method**: `ExpenseTypeController@getExpenseTypesBySourceId`
- **Description**: Retrieves expense types associated with a specific source ID.
- **Parameters**: Source ID in path
- **Response**: JSON array of expense types.

#### PUT /api/expense-types/{id}
- **Controller Method**: `ExpenseTypeController@update`
- **Description**: Updates an expense type by ID.
- **Parameters**: Expense type data, ID in path
- **Response**: JSON updated expense type.

#### DELETE /api/expense-types/{id}
- **Controller Method**: `ExpenseTypeController@destroy`
- **Description**: Deletes an expense type by ID.
- **Parameters**: ID in path
- **Response**: JSON confirmation.

### Transaction Types

#### GET /api/transaction-types
- **Controller Method**: `TransactionTypesController@index`
- **Description**: Retrieves all transaction types.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/transaction-types
- **Controller Method**: `TransactionTypesController@store`
- **Description**: Creates a new transaction type.
- **Parameters**: Transaction type data
- **Response**: JSON created.

#### GET /api/transaction-types/{id}
- **Controller Method**: `TransactionTypesController@show`
- **Description**: Retrieves a transaction type by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/transaction-types/{id}
- **Controller Method**: `TransactionTypesController@update`
- **Description**: Updates a transaction type.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/transaction-types/{id}
- **Controller Method**: `TransactionTypesController@destroy`
- **Description**: Deletes a transaction type.
- **Parameters**: ID
- **Response**: JSON.

### Points of Contact

#### GET /api/points-of-contact
- **Controller Method**: `PointsOfContactController@index`
- **Description**: Retrieves all points of contact.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/points-of-contact
- **Controller Method**: `PointsOfContactController@store`
- **Description**: Creates a new point of contact.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/points-of-contact/{id}
- **Controller Method**: `PointsOfContactController@show`
- **Description**: Retrieves a point of contact by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/points-of-contact/{id}
- **Controller Method**: `PointsOfContactController@update`
- **Description**: Updates a point of contact.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/points-of-contact/{id}
- **Controller Method**: `PointsOfContactController@destroy`
- **Description**: Deletes a point of contact.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/points-of-contact/by-subcategory/{subCatId}
- **Controller Method**: `PointsOfContactController@getBySubCategory`
- **Description**: Retrieves points of contact by subcategory ID.
- **Parameters**: subCatId
- **Response**: JSON array.

### Payment Channels

#### GET /api/payment-mode-list
- **Controller Method**: `PaymentChannelController@getPaymentModeList`
- **Description**: Retrieves list of payment modes.
- **Parameters**: None
- **Response**: JSON array.

#### GET /api/payment-channels
- **Controller Method**: `PaymentChannelController@index`
- **Description**: Retrieves all payment channels.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/payment-channels
- **Controller Method**: `PaymentChannelController@store`
- **Description**: Creates a new payment channel.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/payment-channels/{id}
- **Controller Method**: `PaymentChannelController@show`
- **Description**: Retrieves a payment channel by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/payment-channels/{id}
- **Controller Method**: `PaymentChannelController@update`
- **Description**: Updates a payment channel.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/payment-channels/{id}
- **Controller Method**: `PaymentChannelController@destroy`
- **Description**: Deletes a payment channel.
- **Parameters**: ID
- **Response**: JSON.

### Payment Channel Details

#### GET /api/payment-channels-details
- **Controller Method**: `PaymentChannelDetailsController@index`
- **Description**: Retrieves all payment channel details.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/payment-channels-details
- **Controller Method**: `PaymentChannelDetailsController@store`
- **Description**: Creates new payment channel details.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/payment-channels-details/{id}
- **Controller Method**: `PaymentChannelDetailsController@show`
- **Description**: Retrieves payment channel details by ID.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/payment-channels-list/
- **Controller Method**: `PaymentChannelDetailsController@getPaymentChannels`
- **Description**: Retrieves list of payment channels.
- **Parameters**: None
- **Response**: JSON array.

#### PUT /api/payment-channels-details/{id}
- **Controller Method**: `PaymentChannelDetailsController@update`
- **Description**: Updates payment channel details.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/payment-channels-details/{id}
- **Controller Method**: `PaymentChannelDetailsController@destroy`
- **Description**: Deletes payment channel details.
- **Parameters**: ID
- **Response**: JSON.

### Account Numbers

#### GET /api/payment-account-number
- **Controller Method**: `AccountNumberController@index`
- **Description**: Retrieves all payment account numbers.
- **Parameters**: None
- **Response**: JSON array.

#### GET /api/account-number-all
- **Controller Method**: `AccountNumberController@showAllAcNo`
- **Description**: Retrieves all account numbers.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/payment-account-number
- **Controller Method**: `AccountNumberController@store`
- **Description**: Creates a new account number.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/payment-account-number/{id}
- **Controller Method**: `AccountNumberController@show`
- **Description**: Retrieves an account number by ID.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/payment/account-numbers/channel/{id}
- **Controller Method**: `AccountNumberController@accountNumbersByChannel`
- **Description**: Retrieves account numbers by payment channel ID.
- **Parameters**: Channel ID
- **Response**: JSON array.

#### GET /api/balance/{account_id}
- **Controller Method**: `AccountNumberController@balanceCheck`
- **Description**: Checks balance for an account ID.
- **Parameters**: account_id
- **Response**: JSON balance.

#### PUT /api/payment-account-number/{id}
- **Controller Method**: `AccountNumberController@update`
- **Description**: Updates an account number.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/payment-account-number/{id}
- **Controller Method**: `AccountNumberController@destroy`
- **Description**: Deletes an account number.
- **Parameters**: ID
- **Response**: JSON.

### Source Categories

#### GET /api/source-category
- **Controller Method**: `SourceCategoryController@index`
- **Description**: Retrieves all source categories.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/source-category
- **Controller Method**: `SourceCategoryController@store`
- **Description**: Creates a new source category.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/source-category/{id}
- **Controller Method**: `SourceCategoryController@show`
- **Description**: Retrieves a source category by ID.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/source-categories/{id}
- **Controller Method**: `SourceCategoryController@getCategoriesBySourceId`
- **Description**: Retrieves categories by source ID.
- **Parameters**: Source ID
- **Response**: JSON array.

#### PUT /api/source-category/{id}
- **Controller Method**: `SourceCategoryController@update`
- **Description**: Updates a source category.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/source-category/{id}
- **Controller Method**: `SourceCategoryController@destroy`
- **Description**: Deletes a source category.
- **Parameters**: ID
- **Response**: JSON.

### Source Subcategories

#### GET /api/source-sub-category
- **Controller Method**: `SourceSubCategoryController@index`
- **Description**: Retrieves all source subcategories.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/source-sub-category
- **Controller Method**: `SourceSubCategoryController@store`
- **Description**: Creates a new source subcategory.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/source-sub-category/{id}
- **Controller Method**: `SourceSubCategoryController@show`
- **Description**: Retrieves a source subcategory by ID.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/source-sub-categories/{id}
- **Controller Method**: `SourceSubCategoryController@getSubCategoriesBySourceId`
- **Description**: Retrieves subcategories by source ID.
- **Parameters**: Source ID
- **Response**: JSON array.

#### PUT /api/source-sub-category/{id}
- **Controller Method**: `SourceSubCategoryController@update`
- **Description**: Updates a source subcategory.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/source-sub-category/{id}
- **Controller Method**: `SourceSubCategoryController@destroy`
- **Description**: Deletes a source subcategory.
- **Parameters**: ID
- **Response**: JSON.

### Source Subcategory Details

#### GET /api/source-sub-category-details
- **Controller Method**: `SourceSubCategoryDetailController@index`
- **Description**: Retrieves all source subcategory details.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/source-sub-category-details
- **Controller Method**: `SourceSubCategoryDetailController@store`
- **Description**: Creates new source subcategory details.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/source-sub-category-details/{id}
- **Controller Method**: `SourceSubCategoryDetailController@show`
- **Description**: Retrieves subcategory details by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/source-sub-category-details/{id}
- **Controller Method**: `SourceSubCategoryDetailController@update`
- **Description**: Updates subcategory details.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/source-sub-category-details/{id}
- **Controller Method**: `SourceSubCategoryDetailController@destroy`
- **Description**: Deletes subcategory details.
- **Parameters**: ID
- **Response**: JSON.

### Postings

#### GET /api/posting
- **Controller Method**: `PostingController@index`
- **Description**: Retrieves all postings.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/posting
- **Controller Method**: `PostingController@store`
- **Description**: Creates a new posting.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/posting/{id}
- **Controller Method**: `PostingController@show`
- **Description**: Retrieves a posting by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/posting/{id}
- **Controller Method**: `PostingController@update`
- **Description**: Updates a posting.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/posting/{id}
- **Controller Method**: `PostingController@destroy`
- **Description**: Deletes a posting.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/posting/status/{id}
- **Controller Method**: `PostingController@statusUpdate`
- **Description**: Updates the status of a posting.
- **Parameters**: ID, status data
- **Response**: JSON.

### Dashboard

#### GET /api/dashboard/financial-summary
- **Controller Method**: `DashboardController@financialSummaryDashboard`
- **Description**: Retrieves financial summary for dashboard.
- **Parameters**: None
- **Response**: JSON summary.

#### GET /api/dashboard/account-balance
- **Controller Method**: `DashboardController@getAccountBalance`
- **Description**: Retrieves account balance data.
- **Parameters**: None
- **Response**: JSON.

#### GET /api/dashboard/total-income-expence
- **Controller Method**: `DashboardController@getTotalIncomeExpense`
- **Description**: Retrieves total income and expense.
- **Parameters**: None
- **Response**: JSON.

#### POST /api/dashboard/total-income-expence-graph
- **Controller Method**: `DashboardController@getTotalIncomeExpenseGraph`
- **Description**: Retrieves graph data for income and expense.
- **Parameters**: Request data for graph
- **Response**: JSON graph data.

#### GET /api/dashboard/total-rental
- **Controller Method**: `DashboardController@getTotalRental`
- **Description**: Retrieves total rental data.
- **Parameters**: None
- **Response**: JSON.

#### POST /api/dashboard/monthly-rental-graph
- **Controller Method**: `DashboardController@getMontlyRentalGraph`
- **Description**: Retrieves monthly rental graph data.
- **Parameters**: Request data
- **Response**: JSON.

#### GET /api/dashboard/investment
- **Controller Method**: `DashboardController@getInvestment`
- **Description**: Retrieves investment data.
- **Parameters**: None
- **Response**: JSON.

#### GET /api/dashboard/currency
- **Controller Method**: `DashboardController@currencySummaryDashboard`
- **Description**: Retrieves currency summary.
- **Parameters**: None
- **Response**: JSON.

#### GET /api/dashboard/total-loan
- **Controller Method**: `DashboardController@getTotalLoan`
- **Description**: Retrieves total loan data.
- **Parameters**: None
- **Response**: JSON.

### Transactions

#### GET /api/transactions
- **Controller Method**: `TransactionController@getTransactions`
- **Description**: Retrieves all transactions.
- **Parameters**: None
- **Response**: JSON array.

#### GET /api/source-category-dropdown
- **Controller Method**: `SourceCategoryController@dropdown`
- **Description**: Retrieves source categories for dropdown.
- **Parameters**: None
- **Response**: JSON array.

#### GET /api/source-subcategory-dropdown
- **Controller Method**: `SourceSubCategoryController@dropdownSubCat`
- **Description**: Retrieves source subcategories for dropdown.
- **Parameters**: None
- **Response**: JSON array.

### Advanced Payments

#### POST /api/advanced-payments-by-point-of-contact-id
- **Controller Method**: `AdvancedPaymentController@advancedPaymentByPointOfContactId`
- **Description**: Retrieves advanced payments by point of contact ID.
- **Parameters**: Point of contact ID in request
- **Response**: JSON.

#### GET /api/advanced-payments
- **Controller Method**: `AdvancedPaymentController@index`
- **Description**: Retrieves all advanced payments.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/advanced-payments
- **Controller Method**: `AdvancedPaymentController@store`
- **Description**: Creates a new advanced payment.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/advanced-payments/{id}
- **Controller Method**: `AdvancedPaymentController@show`
- **Description**: Retrieves an advanced payment by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/advanced-payments/{id}
- **Controller Method**: `AdvancedPaymentController@update`
- **Description**: Updates an advanced payment.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/advanced-payments/{id}
- **Controller Method**: `AdvancedPaymentController@destroy`
- **Description**: Deletes an advanced payment.
- **Parameters**: ID
- **Response**: JSON.

### Currency Trading

#### GET /api/currency-trading/currency-list
- **Controller Method**: `CurrencyController@getAllCurrencies`
- **Description**: Retrieves all currencies.
- **Parameters**: None
- **Response**: JSON array.

#### GET /api/currency-trading/currency
- **Controller Method**: `CurrencyController@index`
- **Description**: Retrieves currencies.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/currency-trading/currency
- **Controller Method**: `CurrencyController@store`
- **Description**: Creates a new currency.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/currency-trading/currency/{id}
- **Controller Method**: `CurrencyController@show`
- **Description**: Retrieves a currency by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/currency-trading/currency/{id}
- **Controller Method**: `CurrencyController@update`
- **Description**: Updates a currency.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/currency-trading/currency/{id}
- **Controller Method**: `CurrencyController@destroy`
- **Description**: Deletes a currency.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/currency-trading/currency-party-list
- **Controller Method**: `CurrencyPartyController@getAllCurrencies`
- **Description**: Retrieves all currency parties.
- **Parameters**: None
- **Response**: JSON array.

#### GET /api/currency-trading/currency-party
- **Controller Method**: `CurrencyPartyController@index`
- **Description**: Retrieves currency parties.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/currency-trading/currency-party
- **Controller Method**: `CurrencyPartyController@store`
- **Description**: Creates a new currency party.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/currency-trading/currency-party/{id}
- **Controller Method**: `CurrencyPartyController@show`
- **Description**: Retrieves a currency party by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/currency-trading/currency-party/{id}
- **Controller Method**: `CurrencyPartyController@update`
- **Description**: Updates a currency party.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/currency-trading/currency-party/{id}
- **Controller Method**: `CurrencyPartyController@destroy`
- **Description**: Deletes a currency party.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/currency-trading/currency-posting
- **Controller Method**: `CurrencyPostingsController@index`
- **Description**: Retrieves all currency postings.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/currency-trading/currency-posting
- **Controller Method**: `CurrencyPostingsController@store`
- **Description**: Creates a new currency posting.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/currency-trading/currency-posting/{id}
- **Controller Method**: `CurrencyPostingsController@show`
- **Description**: Retrieves a currency posting by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/currency-trading/currency-posting/{id}
- **Controller Method**: `CurrencyPostingsController@update`
- **Description**: Updates a currency posting.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/currency-trading/currency-posting/{id}
- **Controller Method**: `CurrencyPostingsController@destroy`
- **Description**: Deletes a currency posting.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/currency-trading/currency-posting/status/{id}
- **Controller Method**: `CurrencyPostingsController@statusUpdate`
- **Description**: Updates status of a currency posting.
- **Parameters**: ID, status
- **Response**: JSON.

#### GET /api/currency-trading/currency-ledger
- **Controller Method**: `CurrencyLedgerController@index`
- **Description**: Retrieves currency ledger.
- **Parameters**: None
- **Response**: JSON.

#### GET /api/currency-trading/currency-analysis
- **Controller Method**: `CurrencyLedgerController@currencyAnalysis`
- **Description**: Retrieves currency analysis.
- **Parameters**: None
- **Response**: JSON.

#### GET /api/currency-trading/currency-ledger-summary
- **Controller Method**: `CurrencyLedgerController@currencyLedgerSummary`
- **Description**: Retrieves currency ledger summary.
- **Parameters**: None
- **Response**: JSON.

### Transfers

#### GET /api/transfer-list
- **Controller Method**: `TransferController@getAllTransfer`
- **Description**: Retrieves all transfers.
- **Parameters**: None
- **Response**: JSON array.

#### GET /api/transfer
- **Controller Method**: `TransferController@index`
- **Description**: Retrieves transfers.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/transfer
- **Controller Method**: `TransferController@store`
- **Description**: Creates a new transfer.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/transfer/{id}
- **Controller Method**: `TransferController@show`
- **Description**: Retrieves a transfer by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/transfer/{id}
- **Controller Method**: `TransferController@update`
- **Description**: Updates a transfer.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/transfer/{id}
- **Controller Method**: `TransferController@destroy`
- **Description**: Deletes a transfer.
- **Parameters**: ID
- **Response**: JSON.

### Income and Expense

#### GET /api/income-expense/head
- **Controller Method**: `IncomeExpenseHeadController@getAllIncomes` and `IncomeExpenseHeadController@index`
- **Description**: Retrieves all income/expense heads.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/income-expense/head
- **Controller Method**: `IncomeExpenseHeadController@store`
- **Description**: Creates a new income/expense head.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/income-expense/head/{id}
- **Controller Method**: `IncomeExpenseHeadController@show`
- **Description**: Retrieves an income/expense head by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/income-expense/head/{id}
- **Controller Method**: `IncomeExpenseHeadController@update`
- **Description**: Updates an income/expense head.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/income-expense/head/{id}
- **Controller Method**: `IncomeExpenseHeadController@destroy`
- **Description**: Deletes an income/expense head.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/income-expense/posting
- **Controller Method**: `IncomeExpensePostingController@index`
- **Description**: Retrieves all income/expense postings.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/income-expense/posting
- **Controller Method**: `IncomeExpensePostingController@store`
- **Description**: Creates a new income/expense posting.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/income-expense/posting/{id}
- **Controller Method**: `IncomeExpensePostingController@show`
- **Description**: Retrieves an income/expense posting by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/income-expense/posting/{id}
- **Controller Method**: `IncomeExpensePostingController@update`
- **Description**: Updates an income/expense posting.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/income-expense/posting/{id}
- **Controller Method**: `IncomeExpensePostingController@destroy`
- **Description**: Deletes an income/expense posting.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/income-expense/posting/status/{id}
- **Controller Method**: `IncomeExpensePostingController@statusUpdate`
- **Description**: Updates status of an income/expense posting.
- **Parameters**: ID, status
- **Response**: JSON.

#### GET /api/income-expense/ledger
- **Controller Method**: `IncomeExpenseLedgerController@getLedgerData`
- **Description**: Retrieves income/expense ledger data.
- **Parameters**: None
- **Response**: JSON.

#### GET /api/income-expense/ledger-summary
- **Controller Method**: `IncomeExpenseLedgerController@getLedgerDataSummary`
- **Description**: Retrieves income/expense ledger summary.
- **Parameters**: None
- **Response**: JSON.

### Loan

#### GET /api/loan/party-all
- **Controller Method**: `LoanBankPartyController@getAllParties`
- **Description**: Retrieves all loan parties.
- **Parameters**: None
- **Response**: JSON array.

#### GET /api/loan/party
- **Controller Method**: `LoanBankPartyController@index`
- **Description**: Retrieves loan parties.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/loan/party
- **Controller Method**: `LoanBankPartyController@store`
- **Description**: Creates a new loan party.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/loan/party/{id}
- **Controller Method**: `LoanBankPartyController@show`
- **Description**: Retrieves a loan party by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/loan/party/{id}
- **Controller Method**: `LoanBankPartyController@update`
- **Description**: Updates a loan party.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/loan/party/{id}
- **Controller Method**: `LoanBankPartyController@destroy`
- **Description**: Deletes a loan party.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/loan/posting
- **Controller Method**: `LoanPostingController@index`
- **Description**: Retrieves all loan postings.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/loan/posting
- **Controller Method**: `LoanPostingController@store`
- **Description**: Creates a new loan posting.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/loan/posting/{id}
- **Controller Method**: `LoanPostingController@show`
- **Description**: Retrieves a loan posting by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/loan/posting/{id}
- **Controller Method**: `LoanPostingController@update`
- **Description**: Updates a loan posting.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/loan/posting/{id}
- **Controller Method**: `LoanPostingController@destroy`
- **Description**: Deletes a loan posting.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/loan/posting/status/{id}
- **Controller Method**: `LoanPostingController@statusUpdate`
- **Description**: Updates status of a loan posting.
- **Parameters**: ID, status
- **Response**: JSON.

#### GET /api/loan/loan-calculation/{loan_party_id}/{interest_rate_date}
- **Controller Method**: `LoanPostingController@getLoanCalculation`
- **Description**: Calculates loan interest and balance for a party and date.
- **Parameters**: loan_party_id, interest_rate_date
- **Response**: JSON calculation.

#### GET /api/loan/ledger
- **Controller Method**: `LoanPostingController@getLoanLedgerData`
- **Description**: Retrieves loan ledger data.
- **Parameters**: None
- **Response**: JSON.

#### GET /api/loan/ledger/summary
- **Controller Method**: `LoanPostingController@getLoanSummary`
- **Description**: Retrieves loan ledger summary.
- **Parameters**: None
- **Response**: JSON.

### Rental

#### POST /api/rental/parties-info
- **Controller Method**: `RentalPartyController@getPartyInfo`
- **Description**: Retrieves party info based on request data.
- **Parameters**: Party data in request
- **Response**: JSON.

#### GET /api/rental/parties-refund-info/{id}
- **Controller Method**: `RentalPartyController@getPartyRefundInfo`
- **Description**: Retrieves refund info for a party by ID.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/rental/house-mappings-by-party/{partyId}
- **Controller Method**: `RentalPartyController@getHouseMappingsByParty`
- **Description**: Retrieves house mappings for a party.
- **Parameters**: partyId
- **Response**: JSON.

#### GET /api/rental/parties-all
- **Controller Method**: `RentalPartyController@getAllParties`
- **Description**: Retrieves all rental parties.
- **Parameters**: None
- **Response**: JSON array.

#### GET /api/rental/parties
- **Controller Method**: `RentalPartyController@index`
- **Description**: Retrieves rental parties.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/rental/parties
- **Controller Method**: `RentalPartyController@store`
- **Description**: Creates a new rental party.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/rental/parties/{id}
- **Controller Method**: `RentalPartyController@show`
- **Description**: Retrieves a rental party by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/rental/parties/{id}
- **Controller Method**: `RentalPartyController@update`
- **Description**: Updates a rental party.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/rental/parties/{id}
- **Controller Method**: `RentalPartyController@destroy`
- **Description**: Deletes a rental party.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/rental/houses-all
- **Controller Method**: `RentalHouseController@getAllHouses`
- **Description**: Retrieves all rental houses.
- **Parameters**: None
- **Response**: JSON array.

#### GET /api/rental/houses
- **Controller Method**: `RentalHouseController@index`
- **Description**: Retrieves rental houses.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/rental/houses
- **Controller Method**: `RentalHouseController@store`
- **Description**: Creates a new rental house.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/rental/houses/{id}
- **Controller Method**: `RentalHouseController@show`
- **Description**: Retrieves a rental house by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/rental/houses/{id}
- **Controller Method**: `RentalHouseController@update`
- **Description**: Updates a rental house.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/rental/houses/{id}
- **Controller Method**: `RentalHouseController@destroy`
- **Description**: Deletes a rental house.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/rental/house-party-mapping-all
- **Controller Method**: `RentalHousePartyMappingController@getAllMappings`
- **Description**: Retrieves all house-party mappings.
- **Parameters**: None
- **Response**: JSON array.

#### GET /api/rental/house-party-mapping
- **Controller Method**: `RentalHousePartyMappingController@index`
- **Description**: Retrieves house-party mappings.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/rental/house-party-mapping
- **Controller Method**: `RentalHousePartyMappingController@store`
- **Description**: Creates a new house-party mapping.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/rental/house-party-mapping/{id}
- **Controller Method**: `RentalHousePartyMappingController@show`
- **Description**: Retrieves a house-party mapping by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/rental/house-party-mapping/{id}
- **Controller Method**: `RentalHousePartyMappingController@update`
- **Description**: Updates a house-party mapping.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/rental/house-party-mapping/{id}
- **Controller Method**: `RentalHousePartyMappingController@destroy`
- **Description**: Deletes a house-party mapping.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/rental/postings
- **Controller Method**: `RentalPostingController@index`
- **Description**: Retrieves all rental postings.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/rental/postings
- **Controller Method**: `RentalPostingController@store`
- **Description**: Creates a new rental posting.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/rental/postings/{id}
- **Controller Method**: `RentalPostingController@show`
- **Description**: Retrieves a rental posting by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/rental/postings/{id}
- **Controller Method**: `RentalPostingController@update`
- **Description**: Updates a rental posting.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/rental/postings/{id}
- **Controller Method**: `RentalPostingController@destroy`
- **Description**: Deletes a rental posting.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/rental/postings/status/{id}
- **Controller Method**: `RentalPostingController@statusUpdate`
- **Description**: Updates status of a rental posting.
- **Parameters**: ID, status
- **Response**: JSON.

#### GET /api/rental/rent-mapping-all
- **Controller Method**: `RentalMappingController@getAllMappings`
- **Description**: Retrieves all rent mappings.
- **Parameters**: None
- **Response**: JSON array.

#### GET /api/rental/rent-mapping
- **Controller Method**: `RentalMappingController@index`
- **Description**: Retrieves rent mappings.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/rental/rent-mapping
- **Controller Method**: `RentalMappingController@store`
- **Description**: Creates a new rent mapping.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/rental/rent-mapping/{id}
- **Controller Method**: `RentalMappingController@show`
- **Description**: Retrieves a rent mapping by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/rental/rent-mapping/{id}
- **Controller Method**: `RentalMappingController@update`
- **Description**: Updates a rent mapping.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/rental/rent-mapping/{id}
- **Controller Method**: `RentalMappingController@destroy`
- **Description**: Deletes a rent mapping.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/rental/party-wise-houses/{id}
- **Controller Method**: `RentalMappingController@getPartyWiseHouses`
- **Description**: Retrieves houses for a party by ID.
- **Parameters**: ID
- **Response**: JSON array.

#### GET /api/rental/ledger
- **Controller Method**: `RentalPostingController@getRentalLedgerData`
- **Description**: Retrieves rental ledger data.
- **Parameters**: None
- **Response**: JSON.

#### GET /api/rental/rental-ledger-summary
- **Controller Method**: `RentalPostingController@getRentalLedgerSummary`
- **Description**: Retrieves rental ledger summary.
- **Parameters**: None
- **Response**: JSON.

### Investment

#### GET /api/investment/party-all
- **Controller Method**: `InvestmentPartyController@getAllParties`
- **Description**: Retrieves all investment parties.
- **Parameters**: None
- **Response**: JSON array.

#### GET /api/investment/party
- **Controller Method**: `InvestmentPartyController@index`
- **Description**: Retrieves investment parties.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/investment/party
- **Controller Method**: `InvestmentPartyController@store`
- **Description**: Creates a new investment party.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/investment/party/{id}
- **Controller Method**: `InvestmentPartyController@show`
- **Description**: Retrieves an investment party by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/investment/party/{id}
- **Controller Method**: `InvestmentPartyController@update`
- **Description**: Updates an investment party.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/investment/party/{id}
- **Controller Method**: `InvestmentPartyController@destroy`
- **Description**: Deletes an investment party.
- **Parameters**: ID
- **Response**: JSON.

#### GET /api/investment/posting
- **Controller Method**: `InvestmentPostingController@index`
- **Description**: Retrieves all investment postings.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/investment/posting
- **Controller Method**: `InvestmentPostingController@store`
- **Description**: Creates a new investment posting.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/investment/posting/{id}
- **Controller Method**: `InvestmentPostingController@show`
- **Description**: Retrieves an investment posting by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/investment/posting/{id}
- **Controller Method**: `InvestmentPostingController@update`
- **Description**: Updates an investment posting.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/investment/posting/{id}
- **Controller Method**: `InvestmentPostingController@destroy`
- **Description**: Deletes an investment posting.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/investment/posting/status/{id}
- **Controller Method**: `InvestmentPostingController@statusUpdate`
- **Description**: Updates status of an investment posting.
- **Parameters**: ID, status
- **Response**: JSON.

#### GET /api/investment/investment-calculation/{investment_party_id}
- **Controller Method**: `InvestmentPostingController@getInvestmentCalculation`
- **Description**: Calculates investment return and balance for a party.
- **Parameters**: investment_party_id
- **Response**: JSON calculation.

#### GET /api/investment/ledger
- **Controller Method**: `InvestmentPostingController@getInvestmentLedgerData`
- **Description**: Retrieves investment ledger data.
- **Parameters**: None
- **Response**: JSON.

#### GET /api/investment/ledger-summary
- **Controller Method**: `InvestmentPostingController@getInvestmentLedgerDataSummary`
- **Description**: Retrieves investment ledger summary.
- **Parameters**: None
- **Response**: JSON.

### Bank Deposit

#### GET /api/bank-deposit/posting
- **Controller Method**: `BankDepositController@index`
- **Description**: Retrieves all bank deposit postings.
- **Parameters**: None
- **Response**: JSON array.

#### POST /api/bank-deposit/posting
- **Controller Method**: `BankDepositController@store`
- **Description**: Creates a new bank deposit posting.
- **Parameters**: Data
- **Response**: JSON.

#### GET /api/bank-deposit/posting/{id}
- **Controller Method**: `BankDepositController@show`
- **Description**: Retrieves a bank deposit posting by ID.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/bank-deposit/posting/{id}
- **Controller Method**: `BankDepositController@update`
- **Description**: Updates a bank deposit posting.
- **Parameters**: Data, ID
- **Response**: JSON.

#### DELETE /api/bank-deposit/posting/{id}
- **Controller Method**: `BankDepositController@destroy`
- **Description**: Deletes a bank deposit posting.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/bank-deposit/posting/status/{id}
- **Controller Method**: `BankDepositController@statusUpdate`
- **Description**: Updates status of a bank deposit posting.
- **Parameters**: ID, status
- **Response**: JSON.

### Additional Routes (No Auth Required)

#### GET /api/ledger-summary
- **Controller Method**: `InvestmentPostingController@getInvestmentLedgerDataSummary`
- **Description**: Retrieves investment ledger summary.
- **Parameters**: None
- **Response**: JSON.

#### GET /api/bank-ledger
- **Controller Method**: `CurrencyLedgerController@bankLedger`
- **Description**: Retrieves bank ledger.
- **Parameters**: None
- **Response**: JSON.

#### GET /api/bank-ledger/summary
- **Controller Method**: `CurrencyLedgerController@bankLedgerSummary`
- **Description**: Retrieves bank ledger summary.
- **Parameters**: None
- **Response**: JSON.

#### GET /api/bank-ledger/details
- **Controller Method**: `CurrencyLedgerController@bankLedgerDetails`
- **Description**: Retrieves bank ledger details.
- **Parameters**: None
- **Response**: JSON.

#### GET /api/currency-analysis
- **Controller Method**: `CurrencyLedgerController@currencyAnalysis`
- **Description**: Retrieves currency analysis.
- **Parameters**: None
- **Response**: JSON.

#### POST /api/currency-trading/currency-posting/{id}/soft-delete
- **Controller Method**: `CurrencyPostingsController@softDelete`
- **Description**: Soft deletes a currency posting.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/currency-trading/currency-posting/{id}/restore
- **Controller Method**: `CurrencyPostingsController@restore`
- **Description**: Restores a soft deleted currency posting.
- **Parameters**: ID
- **Response**: JSON.

#### POST /api/bank-deposit/posting/{id}/soft-delete
- **Controller Method**: `BankDepositController@softDelete`
- **Description**: Soft deletes a bank deposit posting.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/bank-deposit/posting/{id}/restore
- **Controller Method**: `BankDepositController@restore`
- **Description**: Restores a soft deleted bank deposit posting.
- **Parameters**: ID
- **Response**: JSON.

#### POST /api/income-expense/posting/{id}/soft-delete
- **Controller Method**: `IncomeExpensePostingController@softDelete`
- **Description**: Soft deletes an income/expense posting.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/income-expense/posting/{id}/restore
- **Controller Method**: `IncomeExpensePostingController@restore`
- **Description**: Restores a soft deleted income/expense posting.
- **Parameters**: ID
- **Response**: JSON.

#### POST /api/loan/posting/{id}/soft-delete
- **Controller Method**: `LoanPostingController@softDelete`
- **Description**: Soft deletes a loan posting.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/loan/posting/{id}/restore
- **Controller Method**: `LoanPostingController@restore`
- **Description**: Restores a soft deleted loan posting.
- **Parameters**: ID
- **Response**: JSON.

#### POST /api/rental/postings/{id}/soft-delete
- **Controller Method**: `RentalPostingController@softDelete`
- **Description**: Soft deletes a rental posting.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/rental/postings/{id}/restore
- **Controller Method**: `RentalPostingController@restore`
- **Description**: Restores a soft deleted rental posting.
- **Parameters**: ID
- **Response**: JSON.

#### POST /api/investment/postings/{id}/soft-delete
- **Controller Method**: `InvestmentPostingController@softDelete`
- **Description**: Soft deletes an investment posting.
- **Parameters**: ID
- **Response**: JSON.

#### PUT /api/investment/postings/{id}/restore
- **Controller Method**: `InvestmentPostingController@restore`
- **Description**: Restores a soft deleted investment posting.
- **Parameters**: ID
- **Response**: JSON.

This documentation covers all routes and their corresponding controller methods. For more detailed implementation, refer to the controller files in `app/Http/Controllers/`. Note that most controllers follow standard CRUD patterns, and specific business logic is implemented in the respective methods.