#!/bin/bash
# This script fetches the latest Tor exit node IPs and saves them to a file.
# Run this as a daily/hourly cron job.

# URL for the list
TOR_LIST_URL="https://check.torproject.org/exit-addresses"

# Local file path
LIST_FILE="/path/to/your/tor_exit_nodes.txt"

# Fetch the list, extract just the IPs, and save
curl -s $TOR_LIST_URL | grep ExitAddress | cut -d ' ' -f 2 > $LIST_FILE