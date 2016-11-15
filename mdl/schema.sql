CREATE TABLE entities (
				id INTEGER PRIMARY KEY,
				entity INTEGER NOT NULL);
CREATE TABLE comments (
				id INTEGER PRIMARY KEY,
				content TEXT NOT NULL,
				reporter TEXT NOT NULL,
				date INTEGER NOT NULL,
				issue INTEGER NOT NULL);
CREATE TABLE causes (
				id INTEGER PRIMARY KEY,
				cause TEXT NOT NULL,
				rootcause INTEGER NOT NULL,
				reporter INTEGER NOT NULL,
				date INTEGER NOT NULL,
				issue INTEGER NOT NULL, potential INTEGER DEFAULT 0);
CREATE TABLE tokens (
				id INTEGER PRIMARY KEY,
				token TEXT NOT NULL,
				page TEXT NOT NULL,
				date INTEGER NOT NULL);
CREATE TABLE tasks_cache (
				id INTEGER PRIMARY KEY,
				task TEXT NOT NULL,
				reason TEXT NULL,
				toupdate INTEGER DEFAULT 0);
CREATE TABLE issues_cache (
				id INTEGER PRIMARY KEY,
				description TEXT NOT NULL,
				opinion TEXT NULL,
				toupdate INTEGER DEFAULT 0);
CREATE TABLE issuetypes (
				id INTEGER PRIMARY KEY,
				pl VARCHAR(100) NOT NULL,
				en VARCHAR(100) NOT NULL);
CREATE TABLE rootcauses (
				id INTEGER PRIMARY KEY,
				pl VARCHAR(100) NOT NULL,
				en VARCHAR(100) NOT NULL);
CREATE TABLE comments_cache (
				id INTEGER PRIMARY KEY,
				content TEXT NOT NULL,
				toupdate INTEGER DEFAULT 0);
CREATE TABLE causes_cache (
				id INTEGER PRIMARY KEY,
				cause TEXT NOT NULL,
				toupdate INTEGER DEFAULT 0);
CREATE TABLE tasktypes (
				id INTEGER PRIMARY KEY,
				pl VARCHAR(100) NOT NULL,
				en VARCHAR(100) NOT NULL, coordinator TEXT NOT NULL DEFAULT 'rolewniczak');
CREATE TABLE tasks (
				id INTEGER PRIMARY KEY,
				task TEXT NOT NULL,
				state INTEGER NOT NULL,
				tasktype INTEGER NULL,
				executor TEXT NOT NULL,
				cost INTEGER NULL,
				reason TEXT NULL,
				reporter TEXT NOT NULL,
				date INTEGER NOT NULL,
				close_date INTEGER NULL,
				cause INTEGER NULL,
				plan_date TEXT NOT NULL,
				all_day_event INTEGET DEFAULT 0,
				start_time TEXT NULL,
				finish_time TEXT NULL,
				issue INTEGER NULL
				, task_cache TEXT NOT NULL DEFAULT '', reason_cache TEXT NULL);
CREATE TABLE issues (
    "id" INTEGER PRIMARY KEY,
    "title" TEXT NOT NULL,
    "description" TEXT NOT NULL,
    "state" INTEGER NOT NULL,
    "opinion" TEXT,
    "type" INTEGER NOT NULL,
    "coordinator" TEXT NOT NULL,
    "reporter" TEXT NOT NULL,
    "date" INTEGER NOT NULL,
    "last_mod" INTEGER NULL,
    "last_activity" TEXT NOT NULL,
    "participants" TEXT NOT NULL
);