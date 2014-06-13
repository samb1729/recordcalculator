f = open("stats")
lines = f.read.lines.map(&:chomp)
f.close

# Each update is 129 lines long
update_length = 129
update_count = lines.length / update_length

updates = Hash.new do |h, k|
  raise "nice wrong key ideut" unless k.kind_of? Integer

  first_line_number = k * update_length
  last_line_number  = first_line_number + update_length - 1

  update = lines[first_line_number..last_line_number]
  update = parse(update).merge({ number: k })

  h[k] = update
end

def parse(update)
  xp_lines = (0..24).inject([]) do |arr, n|
    arr << update[7 + 2 * n].strip[4..-1].to_i
  end

  rank_lines = (0..24).inject([]) do |arr, n|
    arr << update[58 + 2 * n].strip[4..-1].to_i
  end

  time = update[107].match(/\"\d+\"/)[0][1..-1].to_i

  { xp: xp_lines, rank: rank_lines, time: Time.at(time) }
end

# Load all the updates into the hash
0.upto(update_count - 1) { |n| updates[n] }

# Convert hash to array of updates
updates = updates.to_a.map(&:last)

def time_pairs(gap, updates)
  updates.inject([]) { |out, u|
    first = u
    # The endpoint for the pair can't possibly be before the endpoint
    # of the preceding pair, so we use either that or the first update
    # as the starting point for the search
    lower_bound = (out.last || [updates.first]).last[:number]

    last = updates[lower_bound..-1].take_while { |u|
      u[:time] - first[:time] <= gap
    }.last

    out << [first, last]
  }
end

def record(gap, updates)
  pairs = time_pairs(gap, updates).map { |p|
    first, last = p.first, p.last
    [first[:time], last[:time], last[:time] - first[:time], last[:xp][0] - first[:xp][0]]
  }.sort_by { |p|
    p.last
  }

  pairs.last
end

[86400, 86400 * 7, 86400 * 31].each do |time|
  puts record(time, updates).inspect
end
