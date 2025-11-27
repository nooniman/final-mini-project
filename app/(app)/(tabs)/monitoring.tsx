/**
 * Monitoring Screen
 * Attendance tracking and monitoring
 */

import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  RefreshControl,
  TouchableOpacity,
  ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../../../src/context/ThemeContext';
import { Layout } from '../../../src/constants/Layout';
import {
  ThemedView,
  ThemedText,
  Card,
  Badge,
  Header,
  EmptyState,
} from '../../../src/components/ui';
import { subjectsApi } from '../../../src/services/api/subjects';

interface AttendanceStats {
  overallRate: number;
  totalClasses: number;
  present: number;
  absent: number;
  late: number;
  excused: number;
}

interface SubjectAttendance {
  id: string;
  code: string;
  name: string;
  totalClasses: number;
  present: number;
  absent: number;
  late: number;
  excused: number;
  rate: number;
  status: string;
}

interface AttendanceRecord {
  date: string;
  subject: string;
  status: string;
  time: string;
}

export default function MonitoringScreen() {
  const { colors } = useTheme();
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [selectedTab, setSelectedTab] = useState<'overview' | 'history'>('overview');
  
  // API data state
  const [attendanceStats, setAttendanceStats] = useState<AttendanceStats>({
    overallRate: 0,
    totalClasses: 0,
    present: 0,
    absent: 0,
    late: 0,
    excused: 0,
  });
  const [subjectAttendance, setSubjectAttendance] = useState<SubjectAttendance[]>([]);
  const [recentAttendance, setRecentAttendance] = useState<AttendanceRecord[]>([]);

  const fetchAttendanceData = useCallback(async () => {
    try {
      console.log('📅 Fetching attendance data...');
      
      // Fetch subjects
      const subjectsResponse = await subjectsApi.getAll();
      const subjects = subjectsResponse.data || [];
      
      // Calculate attendance for each subject
      // For now, simulate attendance data based on subjects
      // In a real app, this would come from an attendance API endpoint
      let totalPresent = 0;
      let totalAbsent = 0;
      let totalLate = 0;
      let totalExcused = 0;
      let totalClasses = 0;
      
      const subjectData: SubjectAttendance[] = subjects.map((s: any, index: number) => {
        // Simulate attendance data (random but consistent)
        const classCount = Math.floor(Math.random() * 8) + 8; // 8-15 classes
        const present = Math.floor(classCount * (0.85 + Math.random() * 0.15)); // 85-100% present
        const absent = Math.floor((classCount - present) * 0.5);
        const late = Math.min(classCount - present - absent, 2);
        const excused = classCount - present - absent - late;
        const rate = (present / classCount) * 100;
        
        totalClasses += classCount;
        totalPresent += present;
        totalAbsent += absent;
        totalLate += late;
        totalExcused += excused;
        
        return {
          id: s.id?.toString() || index.toString(),
          code: s.code || `SUBJ ${index + 1}`,
          name: s.name || 'Unknown Subject',
          totalClasses: classCount,
          present: present,
          absent: absent,
          late: late,
          excused: excused,
          rate: Math.round(rate * 10) / 10,
          status: rate >= 90 ? 'good' : rate >= 80 ? 'warning' : 'danger',
        };
      });
      
      setSubjectAttendance(subjectData);
      
      // Calculate overall stats
      const overallRate = totalClasses > 0 
        ? Math.round((totalPresent / totalClasses) * 1000) / 10 
        : 0;
      
      setAttendanceStats({
        overallRate: overallRate,
        totalClasses: totalClasses,
        present: totalPresent,
        absent: totalAbsent,
        late: totalLate,
        excused: totalExcused,
      });
      
      // Generate recent attendance history
      const history: AttendanceRecord[] = [];
      const today = new Date();
      
      subjectData.slice(0, 5).forEach((subject, idx) => {
        const statuses = ['present', 'present', 'present', 'late', 'absent'];
        const date = new Date(today);
        date.setDate(date.getDate() - idx);
        
        history.push({
          date: date.toISOString().split('T')[0],
          subject: subject.code,
          status: statuses[idx % 5],
          time: statuses[idx % 5] === 'present' ? '8:00 AM' : statuses[idx % 5] === 'late' ? '8:15 AM' : '-',
        });
      });
      
      setRecentAttendance(history);
      
    } catch (error) {
      console.error('❌ Error fetching attendance data:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchAttendanceData();
  }, [fetchAttendanceData]);

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchAttendanceData();
    setRefreshing(false);
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'present':
        return colors.success;
      case 'absent':
        return colors.error;
      case 'late':
        return colors.warning;
      case 'excused':
        return colors.info;
      default:
        return colors.textTertiary;
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'present':
        return 'checkmark-circle';
      case 'absent':
        return 'close-circle';
      case 'late':
        return 'time';
      case 'excused':
        return 'document-text';
      default:
        return 'help-circle';
    }
  };

  const getRateStatus = (rate: number): 'success' | 'warning' | 'error' => {
    if (rate >= 90) return 'success';
    if (rate >= 80) return 'warning';
    return 'error';
  };

  const formatDate = (dateStr: string) => {
    const date = new Date(dateStr);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date.toDateString() === today.toDateString()) {
      return 'Today';
    } else if (date.toDateString() === yesterday.toDateString()) {
      return 'Yesterday';
    } else {
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
  };

  if (loading) {
    return (
      <ThemedView style={[styles.container, styles.loadingContainer]}>
        <ActivityIndicator size="large" color={colors.primary} />
        <ThemedText variant="subheadline" color="secondary" style={{ marginTop: 16 }}>
          Loading attendance...
        </ThemedText>
      </ThemedView>
    );
  }

  return (
    <ThemedView style={styles.container}>
      <Header title="Attendance" large />

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={colors.primary}
          />
        }
      >
        {/* Overall Stats Card */}
        <Card variant="elevated" style={styles.statsCard}>
          <View style={styles.statsHeader}>
            <View style={styles.mainStat}>
              <ThemedText variant="caption1" color="secondary">
                Overall Attendance
              </ThemedText>
              <ThemedText variant="largeTitle" color="success">
                {attendanceStats.overallRate}%
              </ThemedText>
            </View>
            <View
              style={[
                styles.progressCircle,
                { borderColor: colors.success },
              ]}
            >
              <Ionicons name="checkmark" size={32} color={colors.success} />
            </View>
          </View>

          <View style={styles.statsDivider} />

          <View style={styles.statsGrid}>
            <View style={styles.statItem}>
              <View style={[styles.statDot, { backgroundColor: colors.success }]} />
              <View>
                <ThemedText variant="headline">{attendanceStats.present}</ThemedText>
                <ThemedText variant="caption2" color="secondary">Present</ThemedText>
              </View>
            </View>
            <View style={styles.statItem}>
              <View style={[styles.statDot, { backgroundColor: colors.error }]} />
              <View>
                <ThemedText variant="headline">{attendanceStats.absent}</ThemedText>
                <ThemedText variant="caption2" color="secondary">Absent</ThemedText>
              </View>
            </View>
            <View style={styles.statItem}>
              <View style={[styles.statDot, { backgroundColor: colors.warning }]} />
              <View>
                <ThemedText variant="headline">{attendanceStats.late}</ThemedText>
                <ThemedText variant="caption2" color="secondary">Late</ThemedText>
              </View>
            </View>
            <View style={styles.statItem}>
              <View style={[styles.statDot, { backgroundColor: colors.info }]} />
              <View>
                <ThemedText variant="headline">{attendanceStats.excused}</ThemedText>
                <ThemedText variant="caption2" color="secondary">Excused</ThemedText>
              </View>
            </View>
          </View>
        </Card>

        {/* Tabs */}
        <View style={[styles.tabs, { backgroundColor: colors.surface }]}>
          <TouchableOpacity
            style={[
              styles.tab,
              selectedTab === 'overview' && { backgroundColor: colors.primary },
            ]}
            onPress={() => setSelectedTab('overview')}
          >
            <ThemedText
              variant="subheadline"
              weight="semibold"
              style={{ color: selectedTab === 'overview' ? '#FFFFFF' : colors.text }}
            >
              By Subject
            </ThemedText>
          </TouchableOpacity>
          <TouchableOpacity
            style={[
              styles.tab,
              selectedTab === 'history' && { backgroundColor: colors.primary },
            ]}
            onPress={() => setSelectedTab('history')}
          >
            <ThemedText
              variant="subheadline"
              weight="semibold"
              style={{ color: selectedTab === 'history' ? '#FFFFFF' : colors.text }}
            >
              History
            </ThemedText>
          </TouchableOpacity>
        </View>

        {selectedTab === 'overview' ? (
          // Subject Attendance
          <View style={styles.section}>
            {subjectAttendance.length === 0 ? (
              <EmptyState
                icon="calendar-outline"
                title="No Attendance Data"
                message="Your attendance records will appear here."
              />
            ) : (
              subjectAttendance.map((subject: SubjectAttendance) => (
                <Card key={subject.id} variant="elevated" style={styles.subjectCard}>
                  <View style={styles.subjectHeader}>
                    <View style={styles.subjectInfo}>
                      <ThemedText variant="headline">{subject.name}</ThemedText>
                      <ThemedText variant="caption1" color="secondary">
                        {subject.code} • {subject.totalClasses} classes
                      </ThemedText>
                    </View>
                    <View style={styles.subjectRate}>
                      <ThemedText variant="title2" color={getRateStatus(subject.rate) === 'success' ? 'success' : 'error'}>
                        {subject.rate}%
                      </ThemedText>
                    </View>
                  </View>

                  {/* Progress Bar */}
                  <View style={styles.progressContainer}>
                    <View style={[styles.progressBar, { backgroundColor: colors.border }]}>
                      <View
                        style={[
                          styles.progressFill,
                          {
                            width: `${(subject.present / subject.totalClasses) * 100}%`,
                            backgroundColor: colors.success,
                          },
                        ]}
                      />
                      <View
                        style={[
                          styles.progressFill,
                          {
                            width: `${(subject.late / subject.totalClasses) * 100}%`,
                            backgroundColor: colors.warning,
                          },
                        ]}
                      />
                    </View>
                  </View>

                  <View style={styles.subjectStats}>
                    <View style={styles.miniStat}>
                      <ThemedText variant="caption1" color="success">
                        {subject.present} Present
                      </ThemedText>
                    </View>
                    {subject.absent > 0 && (
                      <View style={styles.miniStat}>
                        <ThemedText variant="caption1" color="error">
                          {subject.absent} Absent
                        </ThemedText>
                      </View>
                    )}
                    {subject.late > 0 && (
                      <View style={styles.miniStat}>
                        <ThemedText variant="caption1" color="muted">
                          {subject.late} Late
                        </ThemedText>
                      </View>
                    )}
                    {subject.excused > 0 && (
                      <View style={styles.miniStat}>
                        <ThemedText variant="caption1" color="muted">
                          {subject.excused} Excused
                        </ThemedText>
                      </View>
                    )}
                  </View>
                </Card>
              ))
            )}
          </View>
        ) : (
          // Attendance History
          <View style={styles.section}>
            {recentAttendance.length === 0 ? (
              <EmptyState
                icon="time-outline"
                title="No History"
                message="Your attendance history will appear here."
              />
            ) : (
              recentAttendance.map((record: AttendanceRecord, index: number) => (
                <View key={index}>
                  {(index === 0 || formatDate(record.date) !== formatDate(recentAttendance[index - 1].date)) && (
                    <ThemedText variant="footnote" color="secondary" style={styles.dateHeader}>
                      {formatDate(record.date)}
                    </ThemedText>
                  )}
                  <Card variant="outlined" style={styles.historyCard}>
                    <View style={styles.historyRow}>
                      <View
                        style={[
                          styles.statusIcon,
                          { backgroundColor: `${getStatusColor(record.status)}20` },
                        ]}
                      >
                        <Ionicons
                          name={getStatusIcon(record.status) as any}
                          size={20}
                          color={getStatusColor(record.status)}
                        />
                      </View>
                      <View style={styles.historyInfo}>
                        <ThemedText variant="headline">{record.subject}</ThemedText>
                        <ThemedText variant="caption1" color="secondary">
                          {record.time !== '-' ? `Time In: ${record.time}` : 'No time recorded'}
                        </ThemedText>
                      </View>
                      <Badge
                        label={record.status.charAt(0).toUpperCase() + record.status.slice(1)}
                        variant={
                          record.status === 'present'
                            ? 'success'
                            : record.status === 'absent'
                            ? 'error'
                            : record.status === 'late'
                            ? 'warning'
                            : 'info'
                        }
                        size="sm"
                      />
                    </View>
                  </Card>
                </View>
              ))
            )}
          </View>
        )}

        <View style={{ height: Layout.spacing.xl }} />
      </ScrollView>
    </ThemedView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  loadingContainer: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: Layout.padding.horizontal,
  },
  statsCard: {
    marginBottom: Layout.spacing.lg,
  },
  statsHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: Layout.spacing.md,
  },
  mainStat: {},
  progressCircle: {
    width: 64,
    height: 64,
    borderRadius: 32,
    borderWidth: 4,
    alignItems: 'center',
    justifyContent: 'center',
  },
  statsDivider: {
    height: StyleSheet.hairlineWidth,
    backgroundColor: '#E0E0E0',
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: Layout.spacing.md,
  },
  statItem: {
    flexDirection: 'row',
    alignItems: 'center',
    width: '50%',
    marginBottom: Layout.spacing.sm,
  },
  statDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    marginRight: Layout.spacing.sm,
  },
  tabs: {
    flexDirection: 'row',
    borderRadius: Layout.borderRadius.md,
    padding: 4,
    marginBottom: Layout.spacing.md,
  },
  tab: {
    flex: 1,
    paddingVertical: Layout.spacing.sm,
    alignItems: 'center',
    borderRadius: Layout.borderRadius.sm,
  },
  section: {
    marginBottom: Layout.spacing.lg,
  },
  subjectCard: {
    marginBottom: Layout.spacing.sm,
    padding: Layout.spacing.md,
  },
  subjectHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  subjectInfo: {
    flex: 1,
    paddingRight: Layout.spacing.md,
  },
  subjectRate: {
    alignItems: 'flex-end',
  },
  progressContainer: {
    marginVertical: Layout.spacing.sm,
  },
  progressBar: {
    height: 6,
    borderRadius: 3,
    flexDirection: 'row',
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
  },
  subjectStats: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: Layout.spacing.sm,
  },
  miniStat: {},
  dateHeader: {
    marginTop: Layout.spacing.md,
    marginBottom: Layout.spacing.xs,
    textTransform: 'uppercase',
  },
  historyCard: {
    marginBottom: Layout.spacing.xs,
  },
  historyRow: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: Layout.spacing.md,
  },
  statusIcon: {
    width: 40,
    height: 40,
    borderRadius: 20,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: Layout.spacing.md,
  },
  historyInfo: {
    flex: 1,
  },
});
