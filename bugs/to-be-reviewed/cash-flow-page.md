# Create a cash flow page showing money in and money out cash flow

**Example cash flow**

Suppose you sell processed PP chips and receive $1,500 cash.

| Transaction    | Cash In | Cash Out |    Balance |
| -------------- | ------: | -------: | ---------: |
| Sales receipt  |  $1,500 |        — | **$1,500** |
| Transport      |       — |     $200 | **$1,300** |
| Rent           |       — |     $300 | **$1,000** |
| Electricity    |       — |     $120 |   **$880** |
| Casual labour  |       — |     $150 |   **$730** |
| Machine repair |       — |      $80 |   **$650** |
The module would therefore show Available Cash Balance = $650.

For each expense, I would capture Date, Expense Number, Category, Description, Amount, Payment Method, Payee/Supplier, Receipt/Reference Number, Related Batch (optional), and Recorded By.

One important design point: expenses shouldn't simply reduce sales revenue. The system should distinguish Revenue, Expenses, and Cash Balance. For example, $1,500 of sales less $850 of expenses gives $650 net after those expenses, while the cash ledger separately shows where the money came from and where it went.